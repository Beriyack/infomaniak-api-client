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
*   **Gestion des Comptes :** Méthode dédiée pour récupérer la liste de vos comptes Infomaniak.
*   **Gestion de la Pagination :** Récupère automatiquement tous les produits, même si l'API les retourne par pages.
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

use App\InfomaniakApiClient; // Utilisation du namespace correct
use App\Product; // Utilisation du namespace correct

// Remplacez par votre véritable token
// Assurez-vous que API_INFOMANIAK est défini, par exemple dans un fichier config.secret.php
// define('API_INFOMANIAK', 'VOTRE_TOKEN_API_ICI'); 
// Ou chargez-le depuis les variables d'environnement.
if (!defined('API_INFOMANIAK')) {
    die("Le token API Infomaniak n'est pas défini. Veuillez le définir dans config.secret.php ou via une variable d'environnement.");
}

$baseUri = 'https://api.infomaniak.com';
$certificatePath = __DIR__ . '/example/USERTrust RSA Certification Authority.crt'; // Optionnel, pour le développement local

try {
    // 1. Initialisez le client
    $apiClient = new InfomaniakApiClient($baseUri, API_INFOMANIAK, $certificatePath);

    // 2. Récupérez les comptes
    echo "--- Comptes Infomaniak ---\n";
    $accountsResponse = $apiClient->getAccounts();
    if (isset($accountsResponse['result']) && $accountsResponse['result'] === 'success') {
        foreach ($accountsResponse['data'] as $account) {
            echo sprintf("ID: %d, Nom: %s\n", $account['id'], $account['name']);
        }
    } else {
        echo "Impossible de récupérer les comptes.\n";
    }
    echo "\n";

    // 3. Récupérez les produits (les 15 premiers par défaut)
    echo "--- Produits Infomaniak (15 premiers) ---\n";
    $productsResponse = $apiClient->get('/1/products');

    // 4. Traitez les résultats
    if (isset($productsResponse['result']) && $productsResponse['result'] === 'success') {
        // Transformez les données brutes en objets Product
        $products = array_map(fn($p) => new Product($p), $productsResponse['data']);

        foreach ($products as $product) {
            echo sprintf(
                "ID: %d, Nom: %s, Service: %s, Expiration: %s\n",
                $product->getId(),
                $product->getCustomerName(),
                $product->getServiceName(),
                $product->getFormattedExpiredAt()
            );
        }
    } else {
        echo "Impossible de récupérer les produits.\n";
    }

} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage();
}
```

---

## 🚀 Application d'Exemple

Pour une démonstration complète, explorez l'application dans le dossier `example/`.

1.  **Configuration :**
    *   Créez un fichier `config.secret.php` à **trois niveaux au-dessus** du dossier `infomaniak-api-client`. Par exemple, si votre projet est dans `d:/projets/infomaniak-api-client/`, le fichier doit être dans `d:/config.secret.php`. 
    *   Ajoutez-y votre token d'API :
        ```php
        <?php
        // config.secret.php
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


## 📧 Contact

Pour toute question ou suggestion, vous pouvez me contacter via [Beriyack](https://github.com/Beriyack).

-----