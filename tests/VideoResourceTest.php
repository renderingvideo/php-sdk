<?php

declare(strict_types=1);

namespace RenderingVideo\SDK\Tests;

use PHPUnit\Framework\TestCase;
use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Response\VideoTask;
use RenderingVideo\SDK\Response\VideoTaskList;

class VideoResourceTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client('sk-test-key');
    }

    public function testVideoTaskStatusMethods(): void
    {
        $task = new VideoTask(
            taskId: 'test123',
            videoTaskId: 'vt_001',
            width: 1920,
            height: 1080,
            duration: 10,
            status: 'completed'
        );

        $this->assertTrue($task->isCompleted());
        $this->assertFalse($task->isRendering());
        $this->assertFalse($task->isFailed());
        $this->assertFalse($task->isCreated());
    }

    public function testVideoTaskRenderingStatus(): void
    {
        $task = new VideoTask(
            taskId: 'test123',
            videoTaskId: 'vt_001',
            width: 1920,
            height: 1080,
            duration: 10,
            status: 'rendering'
        );

        $this->assertFalse($task->isCompleted());
        $this->assertTrue($task->isRendering());
    }

    public function testVideoTaskFromApiResponse(): void
    {
        $data = [
            'success' => true,
            'taskId' => 'abc123def456',
            'videoTaskId' => 'vt_001',
            'width' => 1920,
            'height' => 1080,
            'duration' => 10,
            'status' => 'completed',
            'videoUrl' => 'https://storage.../videos/abc123.mp4',
            'costCredits' => 15,
        ];

        $task = VideoTask::fromArray($data);

        $this->assertEquals('abc123def456', $task->taskId);
        $this->assertEquals('vt_001', $task->videoTaskId);
        $this->assertEquals(1920, $task->width);
        $this->assertEquals(1080, $task->height);
        $this->assertEquals(10.0, $task->duration);
        $this->assertEquals('completed', $task->status);
        $this->assertEquals('https://storage.../videos/abc123.mp4', $task->videoUrl);
    }
}
