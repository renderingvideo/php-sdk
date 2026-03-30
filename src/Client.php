<?php

declare(strict_types=1);

namespace RenderingVideo\SDK;

use GuzzleHttp\Exception\GuzzleException;
use RenderingVideo\SDK\Exceptions\ApiException;
use RenderingVideo\SDK\Exceptions\AuthenticationException;
use RenderingVideo\SDK\Exceptions\InsufficientCreditsException;
use RenderingVideo\SDK\Exceptions\NotFoundException;
use RenderingVideo\SDK\Exceptions\ValidationException;
use RenderingVideo\SDK\Resources\VideoResource;
use RenderingVideo\SDK\Resources\FileResource;
use RenderingVideo\SDK\Resources\PreviewResource;
use RenderingVideo\SDK\Resources\CreditsResource;

/**
 * RenderingVideo API Client
 *
 * @property-read VideoResource $video Video resource operations
 * @property-read FileResource $files File resource operations
 * @property-read PreviewResource $preview Preview resource operations
 * @property-read CreditsResource $credits Credits resource operations
 */
class Client
{
    private const DEFAULT_BASE_URL = 'https://renderingvideo.com';
    private const DEFAULT_TIMEOUT = 30;

    /**
     * @var \Psr\Http\Client\ClientInterface HTTP client instance
     */
    private object $httpClient;
    private string $apiKey;
    private string $baseUrl;

    private ?VideoResource $video = null;
    private ?FileResource $files = null;
    private ?PreviewResource $preview = null;
    private ?CreditsResource $credits = null;

    /**
     * Create a new RenderingVideo client instance
     *
     * @param string $apiKey Your API key (starts with sk-)
     * @param array $options Additional options:
     *   - base_url: Custom API base URL (default: https://renderingvideo.com)
     *   - timeout: Request timeout in seconds (default: 30)
     *   - http_client: Custom Guzzle HTTP client instance
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->validateApiKey($apiKey);
        $this->apiKey = $apiKey;
        $this->baseUrl = $options['base_url'] ?? self::DEFAULT_BASE_URL;

        /** @phpstan-ignore-next-line */
        $this->httpClient = $options['http_client'] ?? new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $options['timeout'] ?? self::DEFAULT_TIMEOUT,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'RenderingVideo-PHP-SDK',
            ],
        ]);
    }

    /**
     * Magic getter for resource access
     */
    public function __get(string $name): object
    {
        return match ($name) {
            'video' => $this->video ??= new VideoResource($this),
            'files' => $this->files ??= new FileResource($this),
            'preview' => $this->preview ??= new PreviewResource($this),
            'credits' => $this->credits ??= new CreditsResource($this),
            default => throw new \InvalidArgumentException("Unknown resource: {$name}"),
        };
    }

    /**
     * Get the underlying HTTP client
     *
     * @return \Psr\Http\Client\ClientInterface
     */
    public function getHttpClient(): object
    {
        return $this->httpClient;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Make a GET request
     *
     * @throws ApiException
     */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Make a POST request
     *
     * @throws ApiException
     */
    public function post(string $uri, array $data = []): array
    {
        return $this->request('POST', $uri, ['json' => $data === [] ? (object) [] : $data]);
    }

    /**
     * Make a POST request with multipart form data (for file uploads)
     *
     * @throws ApiException
     */
    public function postMultipart(string $uri, array $multipart): array
    {
        return $this->request('POST', $uri, ['multipart' => $multipart]);
    }

    /**
     * Make a DELETE request
     *
     * @throws ApiException
     */
    public function delete(string $uri): array
    {
        return $this->request('DELETE', $uri);
    }

    /**
     * Make an HTTP request
     *
     * @throws ApiException
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Invalid JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (GuzzleException $e) {
            throw $this->convertException($e);
        }
    }

    /**
     * Validate API key format
     */
    private function validateApiKey(string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new AuthenticationException('API key is required');
        }

        if (!str_starts_with($apiKey, 'sk-')) {
            throw new AuthenticationException('Invalid API key format. API key should start with "sk-"');
        }
    }

    /**
     * Convert Guzzle exception to SDK exception
     */
    private function convertException(GuzzleException $e): ApiException
    {
        $response = $e->getResponse();
        $statusCode = $response?->getStatusCode() ?? 0;

        $errorData = [];
        if ($response) {
            $body = $response->getBody()->getContents();
            $errorData = json_decode($body, true) ?? [];
        }

        $errorCode = $errorData['code'] ?? 'UNKNOWN_ERROR';
        $errorMessage = $errorData['error'] ?? $e->getMessage();

        return match ($statusCode) {
            400 => new ValidationException($errorMessage, $errorCode, $statusCode, $errorData['details'] ?? []),
            401 => new AuthenticationException($errorMessage, $errorCode, $statusCode),
            402 => new InsufficientCreditsException($errorMessage, $errorCode, $statusCode),
            404 => new NotFoundException($errorMessage, $errorCode, $statusCode),
            default => new ApiException($errorMessage, $errorCode, $statusCode),
        };
    }
}
