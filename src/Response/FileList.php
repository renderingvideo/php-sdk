<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Response;

/**
 * File list response model
 */
class FileList
{
    /**
     * @param UploadedFile[] $files
     */
    public function __construct(
        public readonly array $files,
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
        $files = array_map(
            fn(array $file) => UploadedFile::fromArray($file),
            $data['files'] ?? []
        );

        $pagination = $data['pagination'] ?? [];

        return new self(
            files: $files,
            page: $pagination['page'] ?? 1,
            limit: $pagination['limit'] ?? 20,
            total: $pagination['total'] ?? count($files),
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
            'files' => array_map(fn(UploadedFile $file) => $file->toArray(), $this->files),
            'pagination' => [
                'page' => $this->page,
                'limit' => $this->limit,
                'total' => $this->total,
            ],
        ];
    }
}
