<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

class AgentResource extends Resource
{
    public function context(): array
    {
        return $this->client->get('/api/agent/v1/context');
    }

    /** Options: page, pageSize, riskLevel, allKeys (requires audit:read:all). */
    public function audit(array $options = []): array
    {
        if (isset($options['allKeys'])) $options['allKeys'] = $options['allKeys'] ? 'true' : 'false';
        return $this->client->get('/api/agent/v1/audit', $options);
    }
}
