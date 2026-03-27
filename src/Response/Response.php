<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Base response interface for all API responses
 */
interface Response
{
    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self;

    /**
     * Convert to array
     */
    public function toArray(): array;
}
