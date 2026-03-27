<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

use RenderingVideo\SDK\Response\VideoTask;
use RenderingVideo\SDK\Response\VideoTaskList;
use RenderingVideo\SDK\Response\RenderResult;

/**
 * Video resource for managing video tasks
 */
class VideoResource extends Resource
{
    /**
     * Create a new video task
     *
     * @param array $config Video configuration following JSON Schema
     * @param array $metadata Optional custom metadata
     * @return VideoTask
     */
    public function create(array $config, array $metadata = []): VideoTask
    {
        $data = ['config' => $config];
        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        $response = $this->client->post('/api/v1/video', $data);

        return VideoTask::fromArray($response);
    }

    /**
     * List all video tasks
     *
     * @param array $options Filter options:
     *   - page: Page number (default: 1)
     *   - limit: Items per page, max 100 (default: 20)
     *   - status: Filter by status (created, rendering, completed, failed)
     * @return VideoTaskList
     */
    public function list(array $options = []): VideoTaskList
    {
        $response = $this->client->get('/api/v1/video', $options);

        return VideoTaskList::fromArray($response);
    }

    /**
     * Get a specific video task by ID
     *
     * @param string $taskId The task ID
     * @return VideoTask
     */
    public function get(string $taskId): VideoTask
    {
        $response = $this->client->get("/api/v1/video/{$taskId}");

        return VideoTask::fromArray($response);
    }

    /**
     * Delete a video task
     *
     * @param string $taskId The task ID
     * @return array Deletion result with 'deleted' and 'remoteDeleted' flags
     */
    public function delete(string $taskId): array
    {
        return $this->client->delete("/api/v1/video/{$taskId}");
    }

    /**
     * Trigger or re-trigger rendering for a video task
     *
     * @param string $taskId The task ID
     * @param array $options Render options:
     *   - webhook_url: URL for completion notification
     *   - num_workers: Number of render workers (default: 5)
     * @return RenderResult
     */
    public function render(string $taskId, array $options = []): RenderResult
    {
        $response = $this->client->post("/api/v1/video/{$taskId}/render", $options);

        return RenderResult::fromArray($response);
    }

    /**
     * Wait for a video task to complete
     *
     * @param string $taskId The task ID
     * @param int $timeout Maximum wait time in seconds (default: 300)
     * @param int $interval Polling interval in seconds (default: 5)
     * @return VideoTask
     * @throws \RuntimeException If timeout is reached
     */
    public function waitForCompletion(string $taskId, int $timeout = 300, int $interval = 5): VideoTask
    {
        $start = time();

        while (true) {
            $task = $this->get($taskId);

            if (in_array($task->status, ['completed', 'failed'], true)) {
                return $task;
            }

            if (time() - $start >= $timeout) {
                throw new \RuntimeException("Timeout waiting for task {$taskId} to complete");
            }

            sleep($interval);
        }
    }
}
