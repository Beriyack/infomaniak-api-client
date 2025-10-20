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

    // On pourrait ajouter ici des méthodes post(), patch(), etc. à l'avenir.
}
