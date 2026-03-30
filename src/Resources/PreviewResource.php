<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

use RenderingVideo\SDK\Response\PreviewTask;
use RenderingVideo\SDK\Response\RenderResult;

/**
 * Preview resource for managing temporary preview links
 */
class PreviewResource extends Resource
{
    /**
     * Create a temporary preview link
     *
     * @param array $config Full video schema sent directly as the request body
     * @return PreviewTask
     */
    public function create(array $config): PreviewTask
    {
        $response = $this->client->post('/api/v1/preview', $config);

        return PreviewTask::fromArray($response);
    }

    /**
     * Get preview config by temp ID
     *
     * @param string $tempId The temporary preview ID
     * @return array Preview configuration
     */
    public function get(string $tempId): array
    {
        $response = $this->client->get("/api/v1/preview/{$tempId}");

        return $response['config'] ?? [];
    }

    /**
     * Delete a temporary preview link
     *
     * @param string $tempId The temporary preview ID
     * @return array Deletion result
     */
    public function delete(string $tempId): array
    {
        return $this->client->delete("/api/v1/preview/{$tempId}");
    }

    /**
     * Convert a preview to a permanent video task
     *
     * @param string $tempId The temporary preview ID
     * @param array $options Conversion options:
     *   - category: Category for the new task
     * @return PreviewTask
     */
    public function convert(string $tempId, array $options = []): PreviewTask
    {
        $response = $this->client->post("/api/v1/preview/{$tempId}/convert", $options);

        return PreviewTask::fromConvertResponse($response);
    }

    /**
     * Convert a preview to permanent task and immediately start rendering
     *
     * @param string $tempId The temporary preview ID
     * @param array $options Render options:
     *   - category: Category for the new task (default: api)
     *   - webhook_url: URL for completion notification
     *   - num_workers: Number of render workers (default: 5)
     * @return RenderResult
     */
    public function render(string $tempId, array $options = []): RenderResult
    {
        $response = $this->client->post("/api/v1/preview/{$tempId}/render", $options);

        return RenderResult::fromArray($response);
    }
}
