# RenderingVideo PHP SDK

Official PHP SDK for the [RenderingVideo](https://renderingvideo.com) API - a powerful video rendering and generation service.

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

Install via Composer:

```bash
composer require renderingvideo/sdk
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use RenderingVideo\SDK\Client;

// Initialize the client with your API key
$client = new Client('sk-your-api-key');
```

## Authentication

Get your API key from [Settings > API Keys](https://renderingvideo.com/settings/apikeys).

```php
$client = new Client('sk-your-api-key', [
    'base_url' => 'https://renderingvideo.com',  // Optional: custom base URL
    'timeout' => 30,                             // Optional: request timeout in seconds
]);
```

## Usage

### Video Tasks

#### Create a Video Task

```php
$task = $client->video->create([
    'meta' => [
        'version' => '2.0.0',
        'width' => 1920,
        'height' => 1080,
        'fps' => 30,
        'background' => '#000000',
    ],
    'tracks' => [
        [
            'clips' => [
                [
                    'type' => 'text',
                    'text' => 'Hello World',
                    'start' => 0,
                    'duration' => 5,
                ],
            ],
        ],
    ],
], [
    'project_id' => 'proj_123',  // Optional metadata
]);

echo $task->taskId;        // abc123def456
echo $task->previewUrl;    // https://video.renderingvideo.com/v/abc123def456
echo $task->status;        // created
```

#### List Video Tasks

```php
$taskList = $client->video->list([
    'page' => 1,
    'limit' => 20,
    'status' => 'completed',  // Filter by status
]);

foreach ($taskList->tasks as $task) {
    echo $task->taskId . ': ' . $task->status . PHP_EOL;
}

echo "Total: " . $taskList->total;
echo "Has more: " . ($taskList->hasMore() ? 'yes' : 'no');
```

#### Get Task Details

```php
$task = $client->video->get('abc123def456');

echo $task->status;      // completed, rendering, created, failed
echo $task->videoUrl;    // Available when completed
echo $task->costCredits; // Credits used
```

#### Trigger Rendering

```php
$renderResult = $client->video->render('abc123def456', [
    'webhook_url' => 'https://your-server.com/webhook',  // Optional
    'num_workers' => 5,                                   // Optional
]);

echo $renderResult->status;           // rendering
echo $renderResult->remainingCredits; // 970
```

#### Wait for Completion

```php
// Blocking call that polls until completion
$task = $client->video->waitForCompletion(
    'abc123def456',
    timeout: 300,   // Max wait time in seconds
    interval: 5,    // Polling interval in seconds
);

if ($task->isCompleted()) {
    echo "Video ready: " . $task->videoUrl;
}
```

#### Delete a Task

```php
$result = $client->video->delete('abc123def456');

echo $result['deleted'];       // true
echo $result['remoteDeleted']; // true
```

### File Uploads

#### Upload Files

```php
// Single file
$result = $client->files->upload('/path/to/image.png');

// Multiple files
$result = $client->files->upload([
    '/path/to/image.png',
    '/path/to/video.mp4',
]);

$firstFile = $result->getFirst();
echo $firstFile->id;            // asset_001
echo $firstFile->url;           // https://storage.../images/...
echo $firstFile->getReadableSize(); // 123.45 KB
```

#### Upload from Content

```php
$content = file_get_contents('/path/to/file.png');
$result = $client->files->uploadFromContent($content, 'my-image.png');
```

#### List Files

```php
$fileList = $client->files->list([
    'page' => 1,
    'limit' => 20,
    'type' => 'image',  // Filter: image, video, audio
]);

foreach ($fileList->files as $file) {
    echo $file->name . ' (' . $file->getReadableSize() . ')' . PHP_EOL;
}
```

#### Delete a File

```php
$result = $client->files->delete('asset_001');
```

### Preview Links

#### Create a Preview

```php
$preview = $client->preview->create([
    'meta' => [
        'version' => '2.0.0',
        'width' => 1920,
        'height' => 1080,
        'fps' => 30,
    ],
    'tracks' => [...],
]);

echo $preview->tempId;     // temp_abc123
echo $preview->previewUrl; // https://video.renderingvideo.com/t/temp_abc123
echo $preview->expiresIn;  // 7d
```

#### Get Preview Config

```php
$config = $client->preview->get('temp_abc123');
```

#### Convert Preview to Permanent Task

```php
$task = $client->preview->convert('temp_abc123', [
    'category' => 'my-category',  // Optional
]);

echo $task->taskId;
```

#### Convert and Render Immediately

```php
$renderResult = $client->preview->render('temp_abc123', [
    'webhook_url' => 'https://your-server.com/webhook',
    'num_workers' => 5,
]);
```

#### Delete Preview

```php
$result = $client->preview->delete('temp_abc123');
```

### Credits

#### Check Balance

```php
$balance = $client->credits->get();

echo $balance->credits;   // 985
echo $balance->currency;  // credits
```

#### Calculate Cost

```php
// Calculate credits needed for a render
$cost = $client->credits->calculateCost(
    duration: 10,   // seconds
    width: 1920,
    height: 1080
);

echo $cost;  // 15 credits (10 seconds × 1.5 multiplier for 1080p)
```

#### Check Affordability

```php
$canAfford = $client->credits->canAfford(10, 1920, 1080);

if ($canAfford) {
    // Proceed with render
}
```

## Error Handling

```php
use RenderingVideo\SDK\Exceptions\ApiException;
use RenderingVideo\SDK\Exceptions\AuthenticationException;
use RenderingVideo\SDK\Exceptions\InsufficientCreditsException;
use RenderingVideo\SDK\Exceptions\NotFoundException;
use RenderingVideo\SDK\Exceptions\ValidationException;

try {
    $task = $client->video->get('invalid-id');
} catch (NotFoundException $e) {
    echo "Task not found: " . $e->getMessage();
} catch (InsufficientCreditsException $e) {
    echo "Not enough credits: " . $e->getMessage();
} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage();
    print_r($e->getDetails());
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (ApiException $e) {
    echo "API error ({$e->getErrorCode()}): " . $e->getMessage();
}
```

### Error Codes

| Code | Exception | Description |
|------|-----------|-------------|
| `MISSING_API_KEY` | AuthenticationException | No API key provided |
| `INVALID_API_KEY` | AuthenticationException | Invalid or revoked API key |
| `INSUFFICIENT_CREDITS` | InsufficientCreditsException | Not enough credits |
| `NOT_FOUND` | NotFoundException | Resource not found |
| `INVALID_CONFIG` | ValidationException | Invalid video configuration |
| `ALREADY_RENDERING` | ValidationException | Task is already rendering |

## Webhook Notifications

When rendering completes or fails, the webhook URL receives a POST request:

```php
// Your webhook endpoint
$payload = json_decode(file_get_contents('php://input'), true);

$taskId = $payload['taskId'];
$status = $payload['status'];      // 'completed' or 'failed'
$videoUrl = $payload['videoUrl'];  // Available on success
$error = $payload['error'];        // Available on failure
$timestamp = $payload['timestamp'];
```

## Credit Calculation

Credits are calculated as:

```
Cost = Duration (seconds) × Quality Multiplier
```

| Quality | Short Edge | Multiplier |
|---------|------------|------------|
| 720p | ≥720px | 1.0 |
| 1080p | ≥1080px | 1.5 |
| 2K | ≥1440px | 2.0 |

## Development

### Run Tests

```bash
composer install
composer test
```

### Code Style

```bash
composer check-style
composer fix-style
```

## License

MIT License. See [LICENSE](LICENSE) for details.

## Support

- Documentation: https://renderingvideo.com/docs
- API Reference: https://renderingvideo.com/docs/api-reference
- Support: support@renderingvideo.com
