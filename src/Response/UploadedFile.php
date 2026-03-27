<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * Uploaded file model
 */
class UploadedFile
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $url,
        public readonly string $type,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly ?string $createdAt = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            url: $data['url'] ?? '',
            type: $data['type'] ?? '',
            mimeType: $data['mimeType'] ?? '',
            size: $data['size'] ?? 0,
            createdAt: $data['createdAt'] ?? null,
        );
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if file is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if file is audio
     */
    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    /**
     * Get file size in human-readable format
     */
    public function getReadableSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'type' => $this->type,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'createdAt' => $this->createdAt,
        ];
    }
}
