<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

use RenderingVideo\SDK\Client;

/**
 * Base resource class for API resources
 */
abstract class Resource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
