<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class InfomaniakApiClient
{
    private Client $client;
    private string $apiToken;

    public function __construct(string $baseUri, string $apiToken, string $certificatePath)
    {
        $this->apiToken = $apiToken;
        $this->client = new Client([
            'base_uri' => $baseUri,
            'timeout'  => 5.0,
            'verify'   => $certificatePath,
        ]);
    }

    /**
     * @throws RequestException
     */
    public function get(string $endpoint, array $queryParams = []): array
    {
        try {
            $response = $this->client->request('GET', $endpoint, [
                'query' => $queryParams,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept'        => 'application/json',
                ],
            ]);

            $body = $response->getBody()->getContents();
            return json_decode($body, true);

        } catch (RequestException $e) {
            // On relance l'exception pour que l'appelant puisse la gérer.
            // On pourrait aussi créer une exception personnalisée.
            throw $e;
        }
    }

    /**
     * Récupère TOUS les produits en gérant automatiquement la pagination de l'API.
     *
     * @param array $userParams Paramètres de requête supplémentaires. 'with' => 'fqdn' est souvent utile.
     * @return array La liste complète des produits.
     * @throws RequestException
     */
    public function getAllProducts(array $userParams = []): array
    {
        $allProducts = [];
        $page = 1;
        $defaultParams = [
            'per_page' => 100, // Utiliser le maximum autorisé par l'API pour minimiser les appels
        ];
        $params = array_merge($defaultParams, $userParams);

        do {
            $params['page'] = $page;
            // L'endpoint est préfixé par /1/ dans l'exemple d'utilisation.
            $pageData = $this->get('/1/products', $params);

            if (isset($pageData['data']) && !empty($pageData['data'])) {
                $allProducts = array_merge($allProducts, $pageData['data']);
            }
            $page++;
        } while (isset($pageData['page']) && $pageData['page'] < $pageData['pages']);

        return $allProducts;
    }

    /**
     * Récupère la liste des comptes.
     *
     * @return array
     * @throws RequestException
     */
    public function getAccounts(): array
    {
        // L'endpoint est préfixé par /1/ dans l'exemple d'utilisation.
        return $this->get('/1/accounts');
    }

    // On pourrait ajouter ici des méthodes post(), patch(), etc. à l'avenir.
}
