<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Response\UploadedFile;

class UploadedFileTest extends TestCase
{
    public function testFileTypeChecks(): void
    {
        $image = new UploadedFile(
            id: 'asset_001',
            name: 'image.png',
            url: 'https://storage.com/image.png',
            type: 'image',
            mimeType: 'image/png',
            size: 123456
        );

        $this->assertTrue($image->isImage());
        $this->assertFalse($image->isVideo());
        $this->assertFalse($image->isAudio());
    }

    public function testReadableSize(): void
    {
        $file = new UploadedFile(
            id: 'asset_001',
            name: 'video.mp4',
            url: 'https://storage.com/video.mp4',
            type: 'video',
            mimeType: 'video/mp4',
            size: 5242880  // 5 MB
        );

        $this->assertEquals('5 MB', $file->getReadableSize());
    }

    public function testFromApiResponse(): void
    {
        $data = [
            'id' => 'asset_001',
            'name' => 'image.png',
            'url' => 'https://storage.com/image.png',
            'type' => 'image',
            'mimeType' => 'image/png',
            'size' => 123456,
            'createdAt' => '2026-03-19T10:00:00Z',
        ];

        $file = UploadedFile::fromArray($data);

        $this->assertEquals('asset_001', $file->id);
        $this->assertEquals('image.png', $file->name);
        $this->assertEquals('https://storage.com/image.png', $file->url);
        $this->assertEquals('image', $file->type);
        $this->assertEquals('image/png', $file->mimeType);
        $this->assertEquals(123456, $file->size);
    }
}
