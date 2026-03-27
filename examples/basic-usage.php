<?php

/**
 * RenderingVideo SDK Example Usage
 *
 * This file demonstrates the basic usage of the RenderingVideo PHP SDK.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use RenderingVideo\SDK\Client;
use RenderingVideo\SDK\Exceptions\ApiException;
use RenderingVideo\SDK\Exceptions\InsufficientCreditsException;
use RenderingVideo\SDK\Exceptions\NotFoundException;

// Initialize the client with your API key
// Get your API key from: https://renderingvideo.com/settings/apikeys
$apiKey = 'sk-your-api-key-here';
$client = new Client($apiKey);

echo "=== RenderingVideo SDK Example ===\n\n";

// 1. Check credits balance
echo "1. Checking credits balance...\n";
try {
    $balance = $client->credits->get();
    echo "   Current balance: {$balance->credits} {$balance->currency}\n\n";
} catch (ApiException $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// 2. Create a video task
echo "2. Creating a video task...\n";
$config = [
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
                    'text' => 'Hello from PHP SDK!',
                    'start' => 0,
                    'duration' => 5,
                    'style' => [
                        'fontSize' => 72,
                        'color' => '#FFFFFF',
                    ],
                ],
            ],
        ],
    ],
];

try {
    $task = $client->video->create($config, ['project' => 'sdk-example']);
    echo "   Task created!\n";
    echo "   Task ID: {$task->taskId}\n";
    echo "   Status: {$task->status}\n";
    echo "   Preview URL: {$task->previewUrl}\n\n";
} catch (ApiException $e) {
    echo "   Error ({$e->getErrorCode()}): {$e->getMessage()}\n\n";
    exit(1);
}

// 3. Calculate render cost
echo "3. Calculating render cost...\n";
$cost = $client->credits->calculateCost(
    duration: 10,
    width: 1920,
    height: 1080
);
echo "   Estimated cost: {$cost} credits (10s × 1.5 for 1080p)\n\n";

// 4. Trigger rendering
echo "4. Starting render...\n";
try {
    $renderResult = $client->video->render($task->taskId, [
        'webhook_url' => 'https://your-server.com/webhook', // Optional
    ]);
    echo "   Render started!\n";
    echo "   Render Task ID: {$renderResult->renderTaskId}\n";
    echo "   Status: {$renderResult->status}\n";
    echo "   Remaining credits: {$renderResult->remainingCredits}\n\n";
} catch (InsufficientCreditsException $e) {
    echo "   Error: Not enough credits to render!\n\n";
} catch (ApiException $e) {
    echo "   Error ({$e->getErrorCode()}): {$e->getMessage()}\n\n";
}

// 5. List video tasks
echo "5. Listing recent video tasks...\n";
try {
    $taskList = $client->video->list([
        'page' => 1,
        'limit' => 5,
    ]);

    echo "   Total tasks: {$taskList->total}\n";
    foreach ($taskList->tasks as $i => $t) {
        $status = strtoupper($t->status);
        echo "   {$i}. {$t->taskId} - {$status}";
        if ($t->videoUrl) {
            echo " - Completed";
        }
        echo "\n";
    }
    echo "\n";
} catch (ApiException $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// 6. Create a preview (doesn't consume credits)
echo "6. Creating a preview link...\n";
try {
    $preview = $client->preview->create($config);
    echo "   Preview created!\n";
    echo "   Temp ID: {$preview->tempId}\n";
    echo "   Preview URL: {$preview->previewUrl}\n";
    echo "   Expires in: {$preview->expiresIn}\n\n";
} catch (ApiException $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// 7. Upload a file (example)
echo "7. File upload example...\n";
echo "   To upload a file:\n";
echo "   \$result = \$client->files->upload('/path/to/image.png');\n";
echo "   \$file = \$result->getFirst();\n";
echo "   echo \$file->url;\n\n";

// 8. Get specific task status
echo "8. Getting task status...\n";
try {
    $currentTask = $client->video->get($task->taskId);
    echo "   Task ID: {$currentTask->taskId}\n";
    echo "   Status: {$currentTask->status}\n";

    if ($currentTask->isCompleted()) {
        echo "   Video URL: {$currentTask->videoUrl}\n";
    } elseif ($currentTask->isRendering()) {
        echo "   Still rendering...\n";
    } elseif ($currentTask->isFailed()) {
        echo "   Render failed!\n";
    }
    echo "\n";
} catch (NotFoundException $e) {
    echo "   Task not found!\n\n";
} catch (ApiException $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

echo "=== Example Complete ===\n";
