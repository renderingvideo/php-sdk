<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Video task response model
 */
class VideoTask
{
    public function __construct(
        public readonly string $taskId,
        public readonly string $videoTaskId,
        public readonly int $width,
        public readonly int $height,
        public readonly float $duration,
        public readonly string $status,
        public readonly ?string $videoUrl = null,
        public readonly ?string $previewUrl = null,
        public readonly ?string $viewerUrl = null,
        public readonly ?string $configUrl = null,
        public readonly ?string $quality = null,
        public readonly ?int $costCredits = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $completedAt = null,
        public readonly array $metadata = [],
        public readonly bool $success = true,
        public readonly ?string $message = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskId: $data['taskId'] ?? '',
            videoTaskId: $data['videoTaskId'] ?? '',
            width: $data['width'] ?? 0,
            height: $data['height'] ?? 0,
            duration: (float) ($data['duration'] ?? 0),
            status: $data['status'] ?? 'created',
            videoUrl: $data['videoUrl'] ?? null,
            previewUrl: $data['previewUrl'] ?? null,
            viewerUrl: $data['viewerUrl'] ?? null,
            configUrl: $data['configUrl'] ?? null,
            quality: $data['quality'] ?? null,
            costCredits: $data['costCredits'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            completedAt: $data['completedAt'] ?? null,
            metadata: $data['metadata'] ?? [],
            success: $data['success'] ?? true,
            message: $data['message'] ?? null,
        );
    }

    /**
     * Check if task is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if task is rendering
     */
    public function isRendering(): bool
    {
        return $this->status === 'rendering';
    }

    /**
     * Check if task failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if task is just created (not yet rendered)
     */
    public function isCreated(): bool
    {
        return $this->status === 'created';
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'taskId' => $this->taskId,
            'videoTaskId' => $this->videoTaskId,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'status' => $this->status,
            'videoUrl' => $this->videoUrl,
            'previewUrl' => $this->previewUrl,
            'viewerUrl' => $this->viewerUrl,
            'configUrl' => $this->configUrl,
            'quality' => $this->quality,
            'costCredits' => $this->costCredits,
            'createdAt' => $this->createdAt,
            'completedAt' => $this->completedAt,
            'metadata' => $this->metadata,
        ];
    }
}
