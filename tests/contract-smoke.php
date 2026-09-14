<?php
require __DIR__ . '/../vendor/autoload.php';
use RenderingVideo\SDK\Client;
function check($value) { if (!$value) throw new RuntimeException('Contract assertion failed'); }
$origin = getenv('RV_TEST_ORIGIN');
check(str_starts_with($origin, 'http://127.0.0.1:'));
$api = new Client('sk-contract', ['base_url' => $origin]);
$config = ['meta' => ['version' => '2.0.0', 'width' => 1280, 'height' => 720, 'fps' => 30], 'tracks' => [['clips' => [['type' => 'text', 'text' => 'SDK launch', 'start' => 0, 'duration' => 2]]]]];
check(in_array('three', $api->getCapabilities()['schema']['clipTypes'], true));
$task = $api->video->create($config, ['campaign' => 'launch'], ['title' => 'SDK launch', 'category' => 'marketing']);
check($task->title === 'SDK launch');
check($api->video->list(['category' => 'all'])->tasks[0]->category === 'marketing');
check($api->preview->create($config)->tempId === 'preview-test');
check(isset($api->preview->get('preview-test')['meta']));
$api->preview->convert('preview-test', ['metadata' => ['campaign' => 'launch']]);
$api->preview->render('preview-test', ['metadata' => ['campaign' => 'launch'], 'num_workers' => 2]);
$api->video->render($task->taskId, ['num_workers' => 2]);
$api->files->uploadFromContent('test', 'test.png');
echo "PHP SDK user API contracts passed\n";
