<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Resources\PreviewResource;
use RenderingVideo\SDK\Response\PreviewTask;

class PreviewResourceTest extends TestCase
{
    public function testCreateSendsFullSchemaAsRequestBody(): void
    {
        $config = [
            'meta' => [
                'version' => '2.0.0',
                'width' => 1920,
                'height' => 1080,
                'fps' => 30,
            ],
            'tracks' => [],
        ];

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('post')
            ->with('/api/v1/preview', $config)
            ->willReturn([
                'success' => true,
                'tempId' => 'temp_abc123',
                'previewUrl' => 'https://video.renderingvideo.com/t/temp_abc123',
                'expiresIn' => '7d',
            ]);

        $resource = new PreviewResource($client);
        $preview = $resource->create($config);

        $this->assertInstanceOf(PreviewTask::class, $preview);
        $this->assertSame('temp_abc123', $preview->tempId);
        $this->assertSame('7d', $preview->expiresIn);
    }
}
