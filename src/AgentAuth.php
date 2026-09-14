<?php

declare(strict_types=1);

namespace RenderingVideo\SDK;

use RenderingVideo\SDK\Exceptions\ApiException;

/** Ed25519 device proof and short-lived agent tokens. Requires ext-sodium. */
class AgentAuth
{
    private string $secretKey;
    private ?string $token = null;
    private float $expiresAt = 0;
    private string $baseUrl;

    public function __construct(private string $agentKey, private array $device, string $baseUrl = 'https://renderingvideo.com')
    {
        if (!function_exists('sodium_crypto_sign_detached')) {
            throw new \RuntimeException('AgentAuth requires the sodium extension');
        }
        $url = parse_url($baseUrl);
        $local = in_array($url['host'] ?? '', ['localhost', '127.0.0.1', '[::1]'], true);
        if (!$url || empty($url['host']) || isset($url['user'], $url['pass']) || isset($url['user']) || isset($url['pass']) ||
            isset($url['query']) || isset($url['fragment']) || !in_array($url['path'] ?? '', ['', '/'], true) ||
            (($url['scheme'] ?? '') !== 'https' && !(($url['scheme'] ?? '') === 'http' && $local))) {
            throw new \InvalidArgumentException('Agent API base_url must be an HTTPS origin (HTTP is allowed for localhost)');
        }
        $this->baseUrl = rtrim($baseUrl, '/');
        if (!str_starts_with($agentKey, 'ak_') || empty(trim($device['id'] ?? ''))) {
            throw new \InvalidArgumentException('An ak_ agent key and persistent device ID are required');
        }
        $der = self::decode($device['privateKey'] ?? '');
        $prefix = hex2bin('302e020100300506032b657004220420');
        if (strlen($der) !== 48 || !str_starts_with($der, $prefix)) {
            throw new \InvalidArgumentException('Device privateKey must be Ed25519 PKCS8 DER');
        }
        $pair = sodium_crypto_sign_seed_keypair(substr($der, 16));
        $this->secretKey = sodium_crypto_sign_secretkey($pair);
        if (self::encode(sodium_crypto_sign_publickey($pair)) !== ($device['publicKey'] ?? '')) {
            throw new \InvalidArgumentException('Device public/private keys do not match');
        }
    }

    private static function encode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private static function decode(string $value): string
    {
        $bytes = base64_decode(strtr($value, '-_', '+/'), true);
        if ($bytes === false) throw new \InvalidArgumentException('Invalid base64url key');
        return $bytes;
    }

    /** Generate once and persist securely. Private key format is shared with the Node/Python SDKs. */
    public static function generateDevice(): array
    {
        $seed = random_bytes(32);
        $pair = sodium_crypto_sign_seed_keypair($seed);
        return ['id' => bin2hex(random_bytes(16)), 'name' => gethostname() ?: 'php-agent',
            'platform' => PHP_OS_FAMILY, 'arch' => php_uname('m'),
            'publicKey' => self::encode(sodium_crypto_sign_publickey($pair)),
            'privateKey' => self::encode(hex2bin('302e020100300506032b657004220420') . $seed)];
    }

    public function getBaseUrl(): string { return $this->baseUrl; }

    public function invalidate(): void { $this->token = null; }

    private function proof(string $credential, string $method, string $path): array
    {
        $timestamp = (string) (int) floor(microtime(true) * 1000);
        $nonce = self::encode(random_bytes(24));
        $payload = implode("\n", ['RV-AGENT-PROOF-V1', strtoupper($method), $path, $timestamp, $nonce,
            self::encode(hash('sha256', $credential, true))]);
        return ['x-agent-device-id' => $this->device['id'], 'x-agent-timestamp' => $timestamp,
            'x-agent-nonce' => $nonce, 'x-agent-signature' => self::encode(sodium_crypto_sign_detached($payload, $this->secretKey))];
    }

    public function headers(string $method, string $path, object $httpClient): array
    {
        if (!preg_match('#^/api/(v1|agent/v1)/#', $path) || str_contains($path, '#')) {
            throw new \InvalidArgumentException('Agent request is outside the configured API origin');
        }
        if ($this->token === null || $this->expiresAt <= microtime(true) + 30) {
            $exchangePath = '/api/agent/token';
            $response = $httpClient->request('POST', $this->baseUrl . $exchangePath, [
                'allow_redirects' => false, 'http_errors' => false,
                'headers' => array_merge(['Authorization' => 'AgentKey ' . $this->agentKey], $this->proof($this->agentKey, 'POST', $exchangePath)),
                'json' => ['device' => ['id' => $this->device['id'], 'publicKey' => $this->device['publicKey'],
                    'name' => $this->device['name'] ?? (gethostname() ?: 'php-agent'),
                    'platform' => $this->device['platform'] ?? PHP_OS_FAMILY, 'arch' => $this->device['arch'] ?? php_uname('m'),
                    'agentVersion' => 'renderingvideo-php-sdk/1.1']],
            ]);
            $data = json_decode((string) $response->getBody(), true);
            $status = $response->getStatusCode();
            if ($status < 200 || $status >= 300 || !is_array($data) || ($data['success'] ?? true) === false) {
                throw new ApiException($data['error'] ?? 'Agent token exchange failed', $data['code'] ?? 'TOKEN_EXCHANGE_FAILED', $status);
            }
            if (!is_string($data['access_token'] ?? null) || !str_starts_with($data['access_token'], 'at_') ||
                (!is_int($data['expires_in'] ?? null) && !is_float($data['expires_in'] ?? null)) ||
                !is_finite((float) $data['expires_in']) || $data['expires_in'] <= 0) {
                throw new ApiException('Invalid agent token response', 'INVALID_RESPONSE');
            }
            $this->token = $data['access_token'];
            $this->expiresAt = microtime(true) + $data['expires_in'];
        }
        return array_merge(['Authorization' => 'Bearer ' . $this->token], $this->proof($this->token, $method, $path));
    }
}
