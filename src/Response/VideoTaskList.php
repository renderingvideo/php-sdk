<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Video task list response model
 */
class VideoTaskList
{
    /**
     * @param VideoTask[] $tasks
     */
    public function __construct(
        public readonly array $tasks,
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total,
        public readonly bool $success = true,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        $tasks = array_map(
            fn(array $task) => VideoTask::fromArray($task),
            $data['tasks'] ?? []
        );

        $pagination = $data['pagination'] ?? [];

        return new self(
            tasks: $tasks,
            page: $pagination['page'] ?? 1,
            limit: $pagination['limit'] ?? 20,
            total: $pagination['total'] ?? count($tasks),
            success: $data['success'] ?? true,
        );
    }

    /**
     * Check if there are more pages
     */
    public function hasMore(): bool
    {
        return ($this->page * $this->limit) < $this->total;
    }

    /**
     * Get total pages
     */
    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'tasks' => array_map(fn(VideoTask $task) => $task->toArray(), $this->tasks),
            'pagination' => [
                'page' => $this->page,
                'limit' => $this->limit,
                'total' => $this->total,
            ],
        ];
    }
}
