<?php

// test_endpoint.php

require __DIR__ . '/../vendor/autoload.php';

use App\InfomaniakApiClient;
use GuzzleHttp\Exception\RequestException;

// --- CONFIGURATION ---
require_once __DIR__ . '/../../../config.secret.php';

// --- ID DU PRODUIT À TESTER ---
// Remplacez cette valeur par l'ID réel d'un de vos produits "hébergement web"
$productIdToTest = 292564;

// --- Initialisation ---
$baseUri = 'https://api.infomaniak.com';
$certificatePath = __DIR__ . '/USERTrust RSA Certification Authority.crt';
$apiClient = new InfomaniakApiClient($baseUri, API_INFOMANIAK, $certificatePath);

echo "<h1>Test de l'endpoint /1/products/{$productIdToTest}</h1>";

try {
    // On utilise la méthode générique `get` pour être sûr de ne pas avoir de traitement intermédiaire
    $productDetails = $apiClient->get('/1/products/' . $productIdToTest);

    echo '<h2>Réponse de l\'API :</h2>';
    echo '<pre>';
    print_r($productDetails);
    echo '</pre>';

} catch (RequestException $e) {
    echo '<h2>Erreur lors de l\'appel API :</h2>';
    if ($e->hasResponse()) {
        echo '<h3>Status Code: ' . $e->getResponse()->getStatusCode() . '</h3>';
        echo '<pre>';
        print_r(json_decode($e->getResponse()->getBody()->getContents(), true));
        echo '</pre>';
    } else {
        echo '<p>Erreur de connexion : ' . $e->getMessage() . '</p>';
    }
}
