<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Exceptions\AuthenticationException;

class ClientTest extends TestCase
{
    public function testConstructorWithValidApiKey(): void
    {
        $client = new Client('sk-test-key');

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorThrowsExceptionForEmptyApiKey(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('API key is required');

        new Client('');
    }

    public function testConstructorThrowsExceptionForInvalidApiKeyFormat(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key format');

        new Client('invalid-key');
    }

    public function testCustomBaseUrl(): void
    {
        $client = new Client('sk-test-key', [
            'base_url' => 'https://custom.api.com',
        ]);

        $this->assertEquals('https://custom.api.com', $client->getBaseUrl());
    }

    public function testResourceAccess(): void
    {
        $client = new Client('sk-test-key');

        $this->assertNotNull($client->video);
        $this->assertNotNull($client->files);
        $this->assertNotNull($client->preview);
        $this->assertNotNull($client->credits);
    }

    public function testInvalidResourceThrowsException(): void
    {
        $client = new Client('sk-test-key');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown resource: invalid');

        $client->invalid;
    }
}
