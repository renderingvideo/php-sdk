<?php
namespace RenderingVideo\SDK\Tests;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\AgentAuth;

class AgentAuthTest extends TestCase
{
    private function decode(string $s): string { return base64_decode(strtr($s, '-_', '+/')); }
    public function testProofBindsExactQueryAndTokenAndRefreshes(): void
    {
        $device = AgentAuth::generateDevice();
        $auth = new AgentAuth('ak_test', $device);
        $mock = new MockHandler([new Response(200, [], '{"access_token":"at_test","expires_in":300}'), new Response(200, [], '{"access_token":"at_test","expires_in":300}')]);
        $stack = HandlerStack::create($mock);
        $history = [];
        $stack->push(Middleware::history($history));
        $http = new HttpClient(['handler' => $stack]);
        $path = '/api/agent/v1/audit?allKeys=true&riskLevel=high%20risk';
        $a = $auth->headers('GET', $path, $http);
        $b = $auth->headers('GET', $path, $http);
        self::assertCount(1, $history);
        self::assertStringNotContainsString('privateKey', (string) $history[0]['request']->getBody());
        self::assertNotSame($a['x-agent-nonce'], $b['x-agent-nonce']);
        $hash = rtrim(strtr(base64_encode(hash('sha256', 'at_test', true)), '+/', '-_'), '=');
        $payload = implode("\n", ['RV-AGENT-PROOF-V1', 'GET', $path, $a['x-agent-timestamp'], $a['x-agent-nonce'], $hash]);
        self::assertTrue(sodium_crypto_sign_verify_detached($this->decode($a['x-agent-signature']), $payload, $this->decode($device['publicKey'])));
        self::assertFalse(sodium_crypto_sign_verify_detached($this->decode($a['x-agent-signature']), str_replace('allKeys=true', 'allKeys=false', $payload), $this->decode($device['publicKey'])));
        $auth->invalidate();
        $auth->headers('GET', $path, $http);
        self::assertCount(2, $history);
    }
    public function testRejectsRemoteHttp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AgentAuth('ak_test', AgentAuth::generateDevice(), 'http://example.com');
    }
    public function testRejectsMismatchedKeys(): void
    {
        $device = AgentAuth::generateDevice();
        $device['publicKey'] = AgentAuth::generateDevice()['publicKey'];
        $this->expectException(\InvalidArgumentException::class);
        new AgentAuth('ak_test', $device);
    }
}
