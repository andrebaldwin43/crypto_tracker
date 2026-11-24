<?php

namespace App\Service\Crypto;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CryptoApiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['CRYPTO_API_KEY'] ?? null;
        $this->apiUrl = $_ENV['CRYPTO_API_URL'] ?? null;

        if (!$this->apiKey || !$this->apiUrl) {
            throw new \RuntimeException('Missing required environment variables: CRYPTO_API_KEY or CRYPTO_API_URL');
        }
    }

    public function getCryptoData(string $endpoint, array $query = []): array
    {
        $response = $this->client->request('GET', $this->apiUrl . '/' . $endpoint, [
            'headers' => [
                'X-CMC_PRO_API_KEY' => $this->apiKey,
            ],
            'query' => $query,
        ]);

        return $response->toArray();
    }
}