<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Preview task response model
 */
class PreviewTask
{
    public function __construct(
        public readonly ?string $tempId = null,
        public readonly ?string $taskId = null,
        public readonly ?string $videoTaskId = null,
        public readonly ?string $previewUrl = null,
        public readonly ?string $viewerUrl = null,
        public readonly ?string $configUrl = null,
        public readonly ?string $expiresIn = null,
        public readonly bool $converted = false,
        public readonly bool $success = true,
        public readonly ?string $message = null,
        public readonly ?string $note = null,
    ) {}

    /**
     * Create from API response array (for create endpoint)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tempId: $data['tempId'] ?? null,
            taskId: $data['taskId'] ?? null,
            videoTaskId: $data['videoTaskId'] ?? null,
            previewUrl: $data['previewUrl'] ?? null,
            viewerUrl: $data['viewerUrl'] ?? null,
            configUrl: $data['configUrl'] ?? null,
            expiresIn: $data['expiresIn'] ?? null,
            converted: $data['converted'] ?? false,
            success: $data['success'] ?? true,
            message: $data['message'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    /**
     * Create from convert response array
     */
    public static function fromConvertResponse(array $data): self
    {
        return new self(
            tempId: $data['tempId'] ?? null,
            taskId: $data['taskId'] ?? null,
            videoTaskId: $data['videoTaskId'] ?? null,
            previewUrl: $data['previewUrl'] ?? null,
            viewerUrl: $data['viewerUrl'] ?? null,
            configUrl: $data['configUrl'] ?? null,
            converted: $data['converted'] ?? true,
            success: $data['success'] ?? true,
            message: $data['message'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'tempId' => $this->tempId,
            'taskId' => $this->taskId,
            'videoTaskId' => $this->videoTaskId,
            'previewUrl' => $this->previewUrl,
            'viewerUrl' => $this->viewerUrl,
            'configUrl' => $this->configUrl,
            'expiresIn' => $this->expiresIn,
            'converted' => $this->converted,
            'message' => $this->message,
            'note' => $this->note,
        ], fn($value) => $value !== null);
    }
}
