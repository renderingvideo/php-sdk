<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Exceptions\AuthenticationException;

final class PublicBoundaryTest extends TestCase
{
    public function testAdministratorCredentialsAreRejected(): void
    {
        foreach (['ak_admin-fixture', 'at_admin-fixture'] as $credential) {
            try {
                new Client($credential);
                $this->fail('Administrator credentials must be rejected');
            } catch (AuthenticationException $error) {
                $this->assertStringContainsString('sk-', $error->getMessage());
            }
        }
        $this->assertFalse(class_exists('RenderingVideo\\SDK\\AgentAuth'));
        $this->assertFalse(class_exists('RenderingVideo\\SDK\\Resources\\AgentResource'));
    }

    public function testNoAdministratorResourceOnUserClient(): void
    {
        $client = new Client('sk-user-fixture');
        $this->expectException(\InvalidArgumentException::class);
        $client->agent;
    }
}
