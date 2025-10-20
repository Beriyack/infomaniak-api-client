# Infomaniak API Client for PHP

Une bibliothèque PHP moderne et orientée objet pour interagir avec l'API d'Infomaniak. Ce client simplifie la communication avec l'API, vous permettant de récupérer et de gérer facilement vos produits (hébergements, domaines, etc.) au sein de vos applications PHP.

Le projet inclut une application d'exemple complète qui démontre des fonctionnalités avancées telles que la mise en cache, le filtrage, la recherche et la pagination.

---

## ✨ Fonctionnalités

*   **Approche Orientée Objet :** Un code propre et maintenable avec des classes dédiées (`InfomaniakApiClient`, `Product`).
*   **Authentification Simplifiée :** Gère automatiquement l'authentification par `Bearer Token` pour toutes les requêtes.
*   **Basé sur Guzzle :** Utilise GuzzleHttp, le standard de l'industrie, pour des communications HTTP fiables.
*   **Données sous forme d'Objets :** Les réponses de l'API pour les produits sont automatiquement transformées en objets `Product`, facilitant la manipulation des données.
*   **Conforme PSR-4 :** Respecte les standards PHP modernes pour l'autoloading et l'interopérabilité.
*   **Application d'Exemple Complète :** Le dossier `example/` contient une interface web prête à l'emploi avec :
    *   Un tableau de bord pour les produits critiques (expirations proches).
    *   Une liste complète des produits avec recherche, filtrage et pagination.
    *   Un système de cache performant pour réduire les appels API.

---

## 📋 Prérequis

*   PHP 8.0 ou supérieur
*   Composer pour la gestion des dépendances
*   Un token d'API Infomaniak. Vous pouvez en générer un depuis votre Manager Infomaniak.

---

## 🛠️ Installation

1.  **Installez la bibliothèque** via Composer depuis la racine de votre projet :

    ```bash
    composer require beriyack/infomaniak-api-client
    ```

2.  **Incluez l'autoloader de Composer** dans votre fichier PHP :

    ```php
    require __DIR__ . '/vendor/autoload.php';
    ```

---

## 📖 Utilisation de Base

Voici un exemple simple pour récupérer vos 15 premiers produits.

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Beriyack\Infomaniak\InfomaniakApiClient;
use Beriyack\Infomaniak\Product;

// Remplacez par votre véritable token
define('API_INFOMANIAK', 'VOTRE_TOKEN_API_ICI');

$baseUri = 'https://api.infomaniak.com';
$certificatePath = __DIR__ . '/example/USERTrust RSA Certification Authority.crt'; // Optionnel, pour le développement local

try {
    // 1. Initialisez le client
    $apiClient = new InfomaniakApiClient($baseUri, API_INFOMANIAK, $certificatePath);

    // 2. Effectuez un appel à l'API
    $response = $apiClient->get('/1/products');

    // 3. Traitez les résultats
    if (isset($response['result']) && $response['result'] === 'success') {
        // Transformez les données brutes en objets Product
        $products = array_map(fn($p) => new Product($p), $response['data']);

        foreach ($products as $product) {
            echo sprintf(
                "ID: %d, Nom: %s, Service: %s\n",
                $product->getId(),
                $product->getCustomerName(),
                $product->getServiceName()
            );
        }
    }

} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}
```

---

## 🚀 Application d'Exemple

Pour une démonstration complète, explorez l'application dans le dossier `example/`.

1.  **Configuration :**
    *   Créez un fichier `config.secret.php` à la racine du projet (au même niveau que le dossier `vendor/`).
    *   Ajoutez-y votre token d'API :
        ```php
        <?php
        define('API_INFOMANIAK', 'VOTRE_TOKEN_API_ICI');
        ```

2.  **Lancement :**
    *   Lancez un serveur web local pointant vers le dossier `example/`.
    *   Ouvrez votre navigateur et accédez à `http://localhost/index.php` ou `http://localhost/products.php`.

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour toute amélioration, correction de bug ou nouvelle fonctionnalité, n'hésitez pas à ouvrir une *issue* ou à soumettre une *pull request*.

---

## 📄 Licence

Ce projet est distribué sous la licence MIT. Voir le fichier `LICENSE` pour plus de détails.
