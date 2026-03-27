<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

use RenderingVideo\SDK\Response\CreditsBalance;

/**
 * Credits resource for checking credit balance
 */
class CreditsResource extends Resource
{
    /**
     * Get current credit balance
     *
     * @return CreditsBalance
     */
    public function get(): CreditsBalance
    {
        $response = $this->client->get('/api/v1/credits');

        return CreditsBalance::fromArray($response);
    }

    /**
     * Check if credits are sufficient for a render
     *
     * @param int $duration Video duration in seconds
     * @param int $width Video width
     * @param int $height Video height
     * @return bool
     */
    public function canAfford(int $duration, int $width, int $height): bool
    {
        $balance = $this->get();
        $required = $this->calculateCost($duration, $width, $height);

        return $balance->credits >= $required;
    }

    /**
     * Calculate the credit cost for a render
     *
     * @param int $duration Video duration in seconds
     * @param int $width Video width
     * @param int $height Video height
     * @return int Required credits
     */
    public function calculateCost(int $duration, int $width, int $height): int
    {
        $shortEdge = min($width, $height);

        // Quality multipliers
        $multiplier = match (true) {
            $shortEdge >= 1440 => 2.0,  // 2K
            $shortEdge >= 1080 => 1.5,  // 1080p
            default => 1.0,              // 720p
        };

        return (int) ceil($duration * $multiplier);
    }
}
