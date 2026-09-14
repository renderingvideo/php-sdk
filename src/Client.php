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
use RenderingVideo\SDK\Resources\AgentResource;
use RenderingVideo\SDK\Exceptions\AlreadyRenderingException;
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
    private ?AgentAuth $agentAuth;
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
    public function __construct(string $apiKey = '', array $options = [])
    {
        $this->agentAuth = $options['agent_auth'] ?? null;
        if ($this->agentAuth && $apiKey !== '') throw new \InvalidArgumentException('Provide apiKey or agent_auth, not both');
        if (!$this->agentAuth) $this->validateApiKey($apiKey);
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($options['base_url'] ?? $this->agentAuth?->getBaseUrl() ?? self::DEFAULT_BASE_URL, '/');
        if ($this->agentAuth && $this->baseUrl !== $this->agentAuth->getBaseUrl()) throw new \InvalidArgumentException('Agent auth and client base_url must match');

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
            'agent' => $this->agentAuth ? new AgentResource($this) : throw new \InvalidArgumentException('Agent operations require AgentAuth'),
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
    public function getCapabilities(): array
    {
        return $this->get('/api/v1/capabilities');
    }

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
        $options['allow_redirects'] = false;
        $options['http_errors'] = false;
        if ($this->agentAuth) {
            $query = $options['query'] ?? [];
            $query = is_array($query) ? \GuzzleHttp\Psr7\Query::build($query) : $query;
            $path = $uri . ($query !== '' ? (str_contains($uri, '?') ? '&' : '?') . $query : '');
            $headers = $this->agentAuth->headers($method, $path, $this->httpClient);
            unset($options['query']);
            $uri = $this->baseUrl . $path;
        } else {
            $headers = ['Authorization' => 'Bearer ' . $this->apiKey];
        }
        $options['headers'] = array_merge($options['headers'] ?? [], $headers);
        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                throw new ApiException('Invalid JSON response: ' . json_last_error_msg());
            }

            $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;
            if ($status < 200 || $status >= 300 || ($data['success'] ?? true) === false) {
                throw $this->apiError($data, $status);
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
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
        $statusCode = $response?->getStatusCode() ?? 0;

        $errorData = [];
        if ($response) {
            $body = $response->getBody()->getContents();
            $errorData = json_decode($body, true) ?? [];
        }

        return $this->apiError(is_array($errorData) ? $errorData : [], $statusCode, $e->getMessage());
    }

    private function apiError(array $data, int $status, string $fallback = 'API request failed'): ApiException
    {
        $code = $data['code'] ?? 'API_ERROR';
        $message = $data['error'] ?? $fallback;
        if ($code === 'ALREADY_RENDERING') return new AlreadyRenderingException($message, $code, $status);
        return match ($status) {
            400 => new ValidationException($message, $code, $status, $data['details'] ?? []),
            401 => new AuthenticationException($message, $code, $status),
            402 => new InsufficientCreditsException($message, $code, $status),
            404 => new NotFoundException($message, $code, $status),
            default => new ApiException($message, $code, $status, $data),
        };
    }
}
