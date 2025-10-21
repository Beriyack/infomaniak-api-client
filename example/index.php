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
$cacheDuration = 43200; // Cache de 12 heures. Les dates d'expiration ne changent pas souvent.
$dataFrom = 'Cache'; // Initialise à 'Cache', sera mis à jour si un appel API est fait

try {
    // --- 1. Récupérer tous les produits (depuis le cache ou l'API) ---
    $productsCacheKey = 'all_products_list';
    $productsCacheFile = $cacheDir . '/' . sha1($productsCacheKey) . '.json';
    $allProducts = null;

    if (Storage::exists($productsCacheFile) && (time() - Storage::lastModified($productsCacheFile)) < $cacheDuration) {
        $allProducts = json_decode(Storage::get($productsCacheFile), true);
    }

    if ($allProducts === null) {
        $dataFrom = 'API Infomaniak';
        // Utilise la nouvelle méthode qui gère la pagination
        $allProducts = $apiClient->getAllProducts(['with' => 'fqdn']);
        Storage::put($productsCacheFile, json_encode($allProducts));
    }

    // --- 2. Transformer toutes les données brutes en objets Product ---
    $productObjects = array_map(fn($p) => new Product($p), $allProducts);

    // --- 3. Filtrer pour ne garder que les produits "critiques" en utilisant la logique de la classe Product ---
    $criticalProducts = array_filter($productObjects, function (Product $product) {
        return $product->isCritical();
    });

    // --- 4. Préparer les données pour la vue ---
    $data = [
        'result' => 'success',
        // Les données sont déjà des objets Product, on ré-indexe juste le tableau après le filtre.
        'data' => array_values($criticalProducts)
    ];

    // --- 5. Récupérer les données annexes (comptes) ---
    $accountsCacheFile = $cacheDir . '/' . sha1('all_accounts') . '.json';
    $accounts = [];
    // Utilise le cache long pour les comptes
    if (Storage::exists($accountsCacheFile) && (time() - Storage::lastModified($accountsCacheFile)) < $cacheDuration) {
        $accountsData = json_decode(Storage::get($accountsCacheFile), true);
        $accounts = isset($accountsData['data']) ? array_column($accountsData['data'], 'name', 'id') : [];
    } else {
        // Si les comptes ne sont pas en cache ou cache expiré, on pourrait les récupérer ici si l'API le permet.
        // Pour l'instant, on laisse vide si pas en cache.
        // TODO: Ajouter un appel API pour récupérer les comptes si nécessaire et les mettre en cache long.
        // Pour l'exemple, on suppose que les comptes ne changent pas souvent et peuvent être mis en cache long.
        // Si l'API Infomaniak a un endpoint pour les comptes, il faudrait l'appeler ici.
        // Par exemple: $accountsData = $apiClient->getAccounts();
        // Storage::put($accountsCacheFile, json_encode($accountsData));
    }

} catch (RequestException $e) {
    // --- Gestion des erreurs ---
    if ($e->hasResponse()) {
        $errorBody = $e->getResponse()->getBody()->getContents();
        $errorData = json_decode($errorBody, true);
        $errorMessage = $errorData['error']['description'] ?? $errorBody;
    } else {
        $errorMessage = $e->getMessage();
    }
}

// Définir un titre pour la page
$pageTitle = "Tableau de bord - Actions critiques";

// Inclure la vue
include __DIR__ . '/dashboard_view.php';
