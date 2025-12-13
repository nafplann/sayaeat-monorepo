<?php

namespace SayaEat\Shared\Clients;

use SayaEat\Shared\Contracts\PyramidClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PyramidClient implements PyramidClientInterface
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $cacheTtl;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        int $timeout = 30,
        int $cacheTtl = 600,
        int $retryTimes = 3,
        int $retrySleep = 100
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->cacheTtl = $cacheTtl;
        $this->retryTimes = $retryTimes;
        $this->retrySleep = $retrySleep;
    }

    /**
     * Send GET request to Pyramid API
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Send POST request to Pyramid API
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Send PUT request to Pyramid API
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * Send DELETE request to Pyramid API
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Send PATCH request to Pyramid API
     */
    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, $data);
    }

    /**
     * Send HTTP request with retry logic
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->buildUrl($endpoint);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep, function ($exception) {
                    // Retry on connection errors or 5xx server errors
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException
                        || ($exception instanceof \Illuminate\Http\Client\RequestException
                            && $exception->response->status() >= 500);
                })
                ->send($method, $url, [
                    $method === 'GET' ? 'query' : 'json' => $data
                ]);

            return $this->handleResponse($response);

        } catch (\Exception $e) {
            Log::error('Pyramid API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \RuntimeException(
                "Pyramid API request failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get request headers
     */
    protected function getHeaders(): array
    {
        return [
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'SayaEat-BFF/1.0',
        ];
    }

    /**
     * Build full URL
     */
    protected function buildUrl(string $endpoint): string
    {
        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }

    /**
     * Handle response and extract data
     */
    protected function handleResponse($response): array
    {
        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error('Pyramid API returned error', [
                'status' => $status,
                'body' => $body
            ]);

            throw new \RuntimeException(
                "Pyramid API error: {$status} - {$body}",
                $status
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Get base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get cache TTL
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }
}

