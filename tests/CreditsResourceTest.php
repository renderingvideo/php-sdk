<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Response\CreditsBalance;

class CreditsResourceTest extends TestCase
{
    public function testCalculateCost720p(): void
    {
        $client = new Client('sk-test-key');

        // 720p: 1x multiplier
        $cost = $client->credits->calculateCost(10, 1280, 720);
        $this->assertEquals(10, $cost);
    }

    public function testCalculateCost1080p(): void
    {
        $client = new Client('sk-test-key');

        // 1080p: 1.5x multiplier
        $cost = $client->credits->calculateCost(10, 1920, 1080);
        $this->assertEquals(15, $cost);
    }

    public function testCalculateCost2K(): void
    {
        $client = new Client('sk-test-key');

        // 2K: 2x multiplier
        $cost = $client->credits->calculateCost(10, 2560, 1440);
        $this->assertEquals(20, $cost);
    }

    public function testCreditsBalanceFromApiResponse(): void
    {
        $data = [
            'success' => true,
            'credits' => 985,
            'currency' => 'credits',
        ];

        $balance = CreditsBalance::fromArray($data);

        $this->assertEquals(985, $balance->credits);
        $this->assertEquals('credits', $balance->currency);
    }
}
