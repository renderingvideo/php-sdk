<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Resources\FileResource;
use RenderingVideo\SDK\Response\UploadResult;

class FileResourceTest extends TestCase
{
    public function testUploadUsesFilesFieldForMultipleFiles(): void
    {
        $fileOne = tempnam(sys_get_temp_dir(), 'rv_');
        $fileTwo = tempnam(sys_get_temp_dir(), 'rv_');
        file_put_contents($fileOne, 'one');
        file_put_contents($fileTwo, 'two');

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('postMultipart')
            ->with(
                '/api/v1/upload',
                $this->callback(function (array $multipart): bool {
                    return count($multipart) === 2
                        && $multipart[0]['name'] === 'files'
                        && $multipart[1]['name'] === 'files';
                })
            )
            ->willReturn([
                'success' => true,
                'count' => 2,
                'assets' => [],
            ]);

        try {
            $resource = new FileResource($client);
            $result = $resource->upload([$fileOne, $fileTwo]);

            $this->assertInstanceOf(UploadResult::class, $result);
            $this->assertSame(2, $result->count);
        } finally {
            @unlink($fileOne);
            @unlink($fileTwo);
        }
    }
}
