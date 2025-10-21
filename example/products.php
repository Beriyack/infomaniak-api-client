<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;
use App\InfomaniakApiClient;
use App\Product;
use Beriyack\Storage;

// --- CONFIGURATION ---
require_once __DIR__ . '/../../../config.secret.php';

$baseUri = 'https://api.infomaniak.com';
$certificatePath = __DIR__ . '/USERTrust RSA Certification Authority.crt';

// --- Initialisation ---
$apiClient = new InfomaniakApiClient($baseUri, API_INFOMANIAK, $certificatePath);
$cacheDir = __DIR__ . '/cache';
$cacheDuration = 3600; // Cache d'une heure, un meilleur compromis.
$dataFrom = '';

try {
    // 1. Récupérer les comptes
    $accountsKey = 'all_accounts';
    $accountsCacheFile = $cacheDir . '/' . sha1($accountsKey) . '.json';
    $accountsData = null;
    
    if (Storage::exists($accountsCacheFile) && (time() - Storage::lastModified($accountsCacheFile)) < $cacheDuration) {
        $accountsData = json_decode(Storage::get($accountsCacheFile), true);
    }

    if ($accountsData === null) {
        $accountsData = $apiClient->getAccounts();
        if (isset($accountsData['result']) && $accountsData['result'] === 'success') {
            Storage::put($accountsCacheFile, json_encode($accountsData));
        }
    }

    $accounts = [];
    if (isset($accountsData['data'])) {
        $accounts = array_column($accountsData['data'], 'name', 'id');
        asort($accounts);
    }

    // 1.bis. Récupérer les types de produits depuis la liste complète
    $productTypesKey = 'all_product_types';
    $productTypesCacheFile = $cacheDir . '/' . sha1($productTypesKey) . '.json';
    $productTypes = null;

    if (Storage::exists($productTypesCacheFile) && (time() - Storage::lastModified($productTypesCacheFile)) < 86400) { // Cache de 24h
        $productTypes = json_decode(Storage::get($productTypesCacheFile), true);
    }

    if ($productTypes === null) {
        // On utilise la méthode qui récupère TOUS les produits pour être sûr d'avoir tous les types.
        $allProductsForTypes = $apiClient->getAllProducts();
        if (!empty($allProductsForTypes)) {
            $productTypes = array_unique(array_column($allProductsForTypes, 'service_name'));
            sort($productTypes);
            Storage::put($productTypesCacheFile, json_encode($productTypes));
        }
    }

    // 2. Récupérer les filtres et paramètres de la page
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    // Utilisation de l'opérateur de coalescence nulle pour simplifier
    $selectedAccountId = !empty($_GET['account_id']) ? (int)$_GET['account_id'] : null;
    $searchName = isset($_GET['search_name']) && !empty($_GET['search_name']) ? $_GET['search_name'] : null;
    $selectedType = isset($_GET['type']) && !empty($_GET['type']) ? $_GET['type'] : null;
    $itemsPerPageOptions = [15, 25, 50, 100];
    $itemsPerPage = in_array((int)($_GET['per_page'] ?? 15), $itemsPerPageOptions) ? (int)($_GET['per_page'] ?? 15) : 15;

    // Si une recherche par nom est effectuée, nous devons récupérer tous les produits,
    // les filtrer, puis les paginer nous-mêmes.
    if ($searchName) {
        // Clé de cache pour la liste complète (sans pagination)
        $fullProductsKey = 'full_products_account_' . ($selectedAccountId ?? 'all') . '_type_' . ($selectedType ?? 'all');
        $fullProductsCacheFile = $cacheDir . '/' . sha1($fullProductsKey) . '.json';
        $allProducts = null;

        if (Storage::exists($fullProductsCacheFile) && (time() - Storage::lastModified($fullProductsCacheFile)) < $cacheDuration) {
            $allProducts = json_decode(Storage::get($fullProductsCacheFile), true);
            $dataFrom = 'Cache';
        }

        if ($allProducts === null) {
            $dataFrom = 'API Infomaniak (recherche complète)';
            
            // On réutilise la méthode existante pour récupérer tous les produits
            $apiParams = [];
            if ($selectedAccountId) $apiParams['account_id'] = $selectedAccountId;
            if ($selectedType) $apiParams['service_name'] = $selectedType;
            $allProducts = $apiClient->getAllProducts($apiParams);
            
            Storage::put($fullProductsCacheFile, json_encode($allProducts));
        }

        // Filtrage en PHP sur la liste complète
        $filteredProducts = array_filter($allProducts, function ($productData) use ($searchName) {
            return strpos(strtolower($productData['customer_name']), strtolower($searchName)) !== false;
        });

        // Pagination manuelle des résultats filtrés
        $totalItems = count($filteredProducts);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedProducts = array_slice($filteredProducts, $offset, $itemsPerPage);

        $data = [
            'result' => 'success',
            'data' => $paginatedProducts,
            'page' => $currentPage,
            'pages' => $totalPages,
            'total' => $totalItems
        ];
    } else {
        // --- Comportement normal (sans recherche par nom) ---
        $productsKey = 'products_account_' . ($selectedAccountId ?? 'all') . '_type_' . ($selectedType ?? 'all') . '_page_' . $currentPage . '_perpage_' . $itemsPerPage;
        $productsCacheFile = $cacheDir . '/' . sha1($productsKey) . '.json';
        $data = null;
        $rawApiData = null;

        if (Storage::exists($productsCacheFile) && (time() - Storage::lastModified($productsCacheFile)) < $cacheDuration) {
            $data = json_decode(Storage::get($productsCacheFile), true);
            $dataFrom = 'Cache';
        }

        if ($data === null) {
            $dataFrom = 'API Infomaniak';
            $queryParams = ['page' => $currentPage, 'per_page' => $itemsPerPage];
            if ($selectedAccountId) $queryParams['account_id'] = $selectedAccountId;
            if ($selectedType) $queryParams['service_name'] = $selectedType;
            
            $rawApiData = $apiClient->get('/1/products', $queryParams);
            $data = $rawApiData; // On continue le traitement avec les mêmes données

            if (isset($data['result']) && $data['result'] === 'success') {
                Storage::put($productsCacheFile, json_encode($data));
            }
        }
    }

    // Assurer que les clés de pagination existent pour l'affichage, même si l'API ne les retourne pas (cas d'une seule page)
    if (isset($data['result']) && $data['result'] === 'success' && !isset($data['page'])) {
        $data['page'] = 1;
        $data['pages'] = 1;
    }

    // Convert raw product data arrays into Product objects
    if (isset($data['data'])) {
        $data['data'] = array_map(function ($productData) {
            return new Product($productData);
        }, $data['data']);
    }

    // Si l'utilisateur demande la vue JSON brute, on utilise les données avant traitement.
    if (isset($_GET['format']) && $_GET['format'] === 'json' && isset($_GET['raw']) && $_GET['raw'] === '1') {
        if ($searchName) {
            // En cas de recherche, la donnée "brute" est la liste complète avant pagination
            $body = json_encode($allProducts);
        } else {
            $body = json_encode($rawApiData ?? $data);
        }
    } else {
        $body = json_encode($data); // Pour la vue JSON traitée ou la vue HTML
    }

} catch (RequestException $e) {
    // Gérer les erreurs
    if ($e->hasResponse()) {
        $errorBody = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($errorBody, true);
        $errorMessage = $errorData['error']['description'] ?? $errorBody;
    } else {
        $errorMessage = $e->getMessage();
    }
}

// --- Logique d'affichage ---
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    include 'json_view.php';
    exit;
}

include 'display.php';
