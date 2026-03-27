<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Resources;

use RenderingVideo\SDK\Response\FileList;
use RenderingVideo\SDK\Response\UploadResult;

/**
 * File resource for managing uploaded files
 */
class FileResource extends Resource
{
    /**
     * Upload one or more files
     *
     * @param string|array $files Single file path or array of file paths
     * @return UploadResult
     */
    public function upload(string|array $files): UploadResult
    {
        $files = is_array($files) ? $files : [$files];
        $multipart = [];

        if (count($files) === 1) {
            $multipart[] = [
                'name' => 'file',
                'contents' => fopen($files[0], 'r'),
                'filename' => basename($files[0]),
            ];
        } else {
            foreach ($files as $file) {
                $multipart[] = [
                    'name' => 'files[]',
                    'contents' => fopen($file, 'r'),
                    'filename' => basename($file),
                ];
            }
        }

        $response = $this->client->postMultipart('/api/v1/upload', $multipart);

        return UploadResult::fromArray($response);
    }

    /**
     * Upload from a stream or string content
     *
     * @param string $content File content
     * @param string $filename Filename to use
     * @return UploadResult
     */
    public function uploadFromContent(string $content, string $filename): UploadResult
    {
        $multipart = [
            [
                'name' => 'file',
                'contents' => $content,
                'filename' => $filename,
            ],
        ];

        $response = $this->client->postMultipart('/api/v1/upload', $multipart);

        return UploadResult::fromArray($response);
    }

    /**
     * List uploaded files
     *
     * @param array $options Filter options:
     *   - page: Page number (default: 1)
     *   - limit: Items per page, max 100 (default: 20)
     *   - type: Filter by type (image, video, audio)
     * @return FileList
     */
    public function list(array $options = []): FileList
    {
        $response = $this->client->get('/api/v1/files', $options);

        return FileList::fromArray($response);
    }

    /**
     * Delete a file
     *
     * @param string $fileId The file ID
     * @return array Deletion result
     */
    public function delete(string $fileId): array
    {
        return $this->client->delete("/api/v1/files/{$fileId}");
    }
}
