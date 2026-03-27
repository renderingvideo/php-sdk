<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Credits balance response model
 */
class CreditsBalance
{
    public function __construct(
        public readonly int $credits,
        public readonly string $currency = 'credits',
        public readonly bool $success = true,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            credits: $data['credits'] ?? 0,
            currency: $data['currency'] ?? 'credits',
            success: $data['success'] ?? true,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'credits' => $this->credits,
            'currency' => $this->currency,
        ];
    }
}
