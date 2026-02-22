<?php

declare(strict_types=1);

namespace KhaleejiAPI;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use KhaleejiAPI\Resources\ValidationResource;
use KhaleejiAPI\Resources\GeoResource;
use KhaleejiAPI\Resources\FinanceResource;
use KhaleejiAPI\Resources\CommunicationResource;
use KhaleejiAPI\Resources\IslamicResource;
use KhaleejiAPI\Resources\UtilityResource;

/**
 * Official PHP SDK for KhaleejiAPI — the MENA region's developer API platform.
 *
 * @property-read ValidationResource    $validation
 * @property-read GeoResource           $geo
 * @property-read FinanceResource       $finance
 * @property-read CommunicationResource $communication
 * @property-read IslamicResource       $islamic
 * @property-read UtilityResource       $utility
 */
class KhaleejiAPI
{
    private HttpClient $http;
    private string $apiKey;
    private string $baseUrl;
    private int $maxRetries;

    public readonly ValidationResource $validation;
    public readonly GeoResource $geo;
    public readonly FinanceResource $finance;
    public readonly CommunicationResource $communication;
    public readonly IslamicResource $islamic;
    public readonly UtilityResource $utility;

    /**
     * Create a new KhaleejiAPI client.
     *
     * @param string $apiKey     Your API key from https://khaleejiapi.dev/dashboard/api-keys
     * @param string $baseUrl    Base URL (default: https://khaleejiapi.dev/api/v1)
     * @param float  $timeout    Request timeout in seconds (default: 30)
     * @param int    $maxRetries Maximum retry attempts on rate limiting (default: 2)
     */
    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://khaleejiapi.dev/api/v1',
        float $timeout = 30.0,
        int $maxRetries = 2,
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->maxRetries = $maxRetries;

        $this->http = new HttpClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'User-Agent' => 'khaleejiapi-php/1.0.0',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->validation = new ValidationResource($this);
        $this->geo = new GeoResource($this);
        $this->finance = new FinanceResource($this);
        $this->communication = new CommunicationResource($this);
        $this->islamic = new IslamicResource($this);
        $this->utility = new UtilityResource($this);
    }

    /**
     * Perform a GET request.
     *
     * @param string               $path   API path
     * @param array<string,string> $params Query parameters
     * @return array<string,mixed>
     * @throws KhaleejiAPIException
     */
    public function get(string $path, array $params = []): array
    {
        // Filter out null values
        $params = array_filter($params, fn($v) => $v !== null && $v !== '');

        return $this->execute('GET', $path, ['query' => $params]);
    }

    /**
     * Perform a POST request.
     *
     * @param string              $path API path
     * @param array<string,mixed> $body Request body
     * @return array<string,mixed>
     * @throws KhaleejiAPIException
     */
    public function post(string $path, array $body = []): array
    {
        return $this->execute('POST', $path, ['json' => $body]);
    }

    /**
     * Execute a request with retry logic.
     *
     * @param string               $method  HTTP method
     * @param string               $path    API path
     * @param array<string,mixed>  $options Guzzle request options
     * @return array<string,mixed>
     * @throws KhaleejiAPIException
     */
    private function execute(string $method, string $path, array $options = []): array
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            if ($attempt > 0) {
                $backoff = (1 << ($attempt - 1)) * 1000000; // microseconds
                usleep($backoff);
            }

            try {
                $response = $this->http->request($method, $path, $options);
                $body = json_decode($response->getBody()->getContents(), true);

                if (isset($body['data'])) {
                    return $body['data'];
                }

                return $body;
            } catch (RequestException $e) {
                $response = $e->getResponse();

                if ($response === null) {
                    throw new KhaleejiAPIException(
                        'Network error: ' . $e->getMessage(),
                        0,
                        'NETWORK_ERROR',
                    );
                }

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents(), true);
                $errorCode = $body['error']['code'] ?? 'SERVER_ERROR';
                $errorMessage = $body['error']['message'] ?? 'Unknown error';

                $rateLimitInfo = [
                    'limit' => $response->getHeaderLine('X-RateLimit-Limit') ?: null,
                    'remaining' => $response->getHeaderLine('X-RateLimit-Remaining') ?: null,
                    'reset' => $response->getHeaderLine('X-RateLimit-Reset') ?: null,
                ];

                if ($statusCode === 429) {
                    $lastException = new KhaleejiAPIException(
                        "Rate limited. Retry after {$rateLimitInfo['reset']} seconds",
                        429,
                        'RATE_LIMITED',
                        $rateLimitInfo,
                    );
                    if ($attempt < $this->maxRetries) {
                        continue;
                    }
                    throw $lastException;
                }

                match ($statusCode) {
                    401 => throw new KhaleejiAPIException('Invalid or missing API key', 401, 'UNAUTHORIZED'),
                    403 => throw new KhaleejiAPIException($errorMessage, 403, 'FORBIDDEN'),
                    404 => throw new KhaleejiAPIException('Resource not found', 404, 'NOT_FOUND'),
                    default => throw new KhaleejiAPIException($errorMessage, $statusCode, $errorCode),
                };
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        throw new KhaleejiAPIException('Unknown error', 500, 'UNKNOWN');
    }
}
