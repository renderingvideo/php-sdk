<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Upload result response model
 */
class UploadResult
{
    /**
     * @param UploadedFile[] $assets
     */
    public function __construct(
        public readonly array $assets,
        public readonly int $count,
        public readonly bool $success = true,
        public readonly ?string $message = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        $assets = array_map(
            fn(array $file) => UploadedFile::fromArray($file),
            $data['assets'] ?? []
        );

        return new self(
            assets: $assets,
            count: $data['count'] ?? count($assets),
            success: $data['success'] ?? true,
            message: $data['message'] ?? null,
        );
    }

    /**
     * Get the first uploaded file (convenience method for single file uploads)
     */
    public function getFirst(): ?UploadedFile
    {
        return $this->assets[0] ?? null;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'count' => $this->count,
            'assets' => array_map(fn(UploadedFile $file) => $file->toArray(), $this->assets),
        ];
    }
}
