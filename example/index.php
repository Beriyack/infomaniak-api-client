<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;
use App\ProductFactory;
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
$cacheDuration = 3600; // Cache d'une heure pour le dashboard
$dataFrom = '';

try {
    // Pour le dashboard, nous récupérons toujours la liste complète des produits
    $fullProductsKey = 'dashboard_full_products';
    $fullProductsCacheFile = $cacheDir . '/' . sha1($fullProductsKey) . '.json';
    $allProducts = null;

    if (Storage::exists($fullProductsCacheFile) && (time() - Storage::lastModified($fullProductsCacheFile)) < $cacheDuration) {
        $allProducts = json_decode(Storage::get($fullProductsCacheFile), true);
        $dataFrom = 'Cache';
    }

    if ($allProducts === null) {
        $dataFrom = 'API Infomaniak (Dashboard)';
        $allProducts = [];
        $apiPage = 1;
        do {
            $pageData = $apiClient->get('/1/products', ['page' => $apiPage, 'per_page' => 100, 'with' => 'fqdn']);
            if (isset($pageData['data']) && !empty($pageData['data'])) {
                $allProducts = array_merge($allProducts, $pageData['data']);
            }
            $apiPage++;
        } while (isset($pageData['page']) && $pageData['page'] < $pageData['pages']);

        // Étape supplémentaire : enrichir les produits avec leurs détails
        foreach ($allProducts as &$product) {
            // On ne cherche les détails que pour les hébergements web
            if ($product['service_name'] === 'web_hosting') {
                $detailsKey = 'product_details_' . $product['id'];
                $detailsCacheFile = $cacheDir . '/' . sha1($detailsKey) . '.json';
                $detailsData = null;

                if (Storage::exists($detailsCacheFile) && (time() - Storage::lastModified($detailsCacheFile)) < $cacheDuration) {
                    $detailsData = json_decode(Storage::get($detailsCacheFile), true);
                } else {
                    $detailsData = $apiClient->get('/1/products/' . $product['id']);
                    Storage::put($detailsCacheFile, json_encode($detailsData));
                }
                $product['details'] = $detailsData['data'] ?? null;
            }
        }
        unset($product); // Important: détruire la référence

        Storage::put($fullProductsCacheFile, json_encode($allProducts));
    }

    // Filtrer pour ne garder que les produits "critiques"
    $criticalProducts = array_filter($allProducts, function ($productData) {
        // Condition 1: Expiration proche (produit ou SSL)
        $productExpiresSoon = isset($productData['expired_at']) && ($productData['expired_at'] - time()) / (60 * 60 * 24) <= 30;
        $sslExpiresSoon = isset($productData['details']['ssl']['expires_on']) && (strtotime($productData['details']['ssl']['expires_on']) - time()) / (60 * 60 * 24) <= 30;

        // Condition 2: Espace disque presque plein
        $diskAlmostFull = false;
        if (isset($productData['details']['quota']['disk_usage'], $productData['details']['quota']['disk_limit']) && $productData['details']['quota']['disk_limit'] > 0) {
            $diskAlmostFull = ($productData['details']['quota']['disk_usage'] / $productData['details']['quota']['disk_limit']) * 100 >= 90;
        }

        return $productExpiresSoon || $sslExpiresSoon || $diskAlmostFull;
    });

    // Préparer les données pour la vue
    $data = [
        'result' => 'success',
        'data' => array_map(fn($p) => new Product($p), array_values($criticalProducts))
    ];

    // On a besoin de la liste des comptes pour l'affichage
    $accountsCacheFile = $cacheDir . '/' . sha1('all_accounts') . '.json';
    $accounts = [];
    if (Storage::exists($accountsCacheFile)) {
        $accountsData = json_decode(Storage::get($accountsCacheFile), true);
        // On s'assure que les données sont valides avant de les utiliser
        $accounts = isset($accountsData['data']) ? array_column($accountsData['data'], 'name', 'id') : [];
    }

} catch (RequestException $e) {
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
