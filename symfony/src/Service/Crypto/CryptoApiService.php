<?php

namespace App\Service\Crypto;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class CryptoApiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(HttpClientInterface $client, private LoggerInterface $logger)
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
        try {
            $response = $this->client->request('GET', $this->apiUrl . '/' . $endpoint, [
                'headers' => [
                    'X-CMC_PRO_API_KEY' => $this->apiKey,
                ],
                'query' => $query,
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->error("API request failed with status {$response->getStatusCode()}", [
                    'endpoint' => $endpoint,
                    'body' => $response->getContent(false)
                ]);
                throw new \RuntimeException("Crypto API returned status {$response->getStatusCode()}");
            }

            return $response->toArray();
        } catch (HttpExceptionInterface $e) {
            $this->logger->error("HTTP error from Crypto API: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'status' => $e->getResponse()->getStatusCode()
            ]);
            throw $e;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error("Transport error calling Crypto API: {$e->getMessage()}", [
                'endpoint' => $endpoint
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error("Unexpected error calling Crypto API: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}