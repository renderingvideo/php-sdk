<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Render result response model
 */
class RenderResult
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $renderTaskId = null,
        public readonly string $status,
        public readonly ?string $quality = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?int $cost = null,
        public readonly ?int $remainingCredits = null,
        public readonly ?string $videoUrl = null,
        public readonly ?string $previewUrl = null,
        public readonly ?string $viewerUrl = null,
        public readonly ?string $configUrl = null,
        public readonly bool $alreadyRendered = false,
        public readonly bool $success = true,
        public readonly ?string $message = null,
        // For preview render response
        public readonly ?string $tempId = null,
        public readonly ?string $convertMessage = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskId: $data['taskId'] ?? '',
            renderTaskId: $data['renderTaskId'] ?? null,
            status: $data['status'] ?? 'rendering',
            quality: $data['quality'] ?? null,
            width: $data['width'] ?? null,
            height: $data['height'] ?? null,
            cost: $data['cost'] ?? null,
            remainingCredits: $data['remainingCredits'] ?? null,
            videoUrl: $data['videoUrl'] ?? null,
            previewUrl: $data['previewUrl'] ?? null,
            viewerUrl: $data['viewerUrl'] ?? null,
            configUrl: $data['configUrl'] ?? null,
            alreadyRendered: $data['alreadyRendered'] ?? false,
            success: $data['success'] ?? true,
            message: $data['message'] ?? null,
            tempId: $data['tempId'] ?? null,
            convertMessage: $data['convertMessage'] ?? null,
        );
    }

    /**
     * Check if render is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if render is in progress
     */
    public function isRendering(): bool
    {
        return $this->status === 'rendering';
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'taskId' => $this->taskId,
            'renderTaskId' => $this->renderTaskId,
            'status' => $this->status,
            'quality' => $this->quality,
            'width' => $this->width,
            'height' => $this->height,
            'cost' => $this->cost,
            'remainingCredits' => $this->remainingCredits,
            'videoUrl' => $this->videoUrl,
            'previewUrl' => $this->previewUrl,
            'viewerUrl' => $this->viewerUrl,
            'configUrl' => $this->configUrl,
            'alreadyRendered' => $this->alreadyRendered,
            'message' => $this->message,
        ], fn($value) => $value !== null);
    }
}
