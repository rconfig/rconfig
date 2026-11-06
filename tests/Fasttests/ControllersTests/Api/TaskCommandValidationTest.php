<?php

use App\Models\Category;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

dataset('unsupported task commands', [
    'command with appended shell text' => ['rconfig:download-device;id'],
    'command with spaced suffix' => ['rconfig:download-device ;id'],
    'command with newline suffix' => ["rconfig:download-device\nid"],
    'command with extra argument' => ['rconfig:download-device 5'],
    'other artisan command' => ['tinker'],
    'unknown command' => ['rconfig:purge-configs'],
]);

/**
 * @return array<string, mixed>
 */
function taskCommandValidationPayload(string $command, array $overrides = []): array
{
    return array_merge([
        'task_name' => 'task-command-' . rand(100000, 999999),
        'task_desc' => 'task command validation',
        'task_command' => $command,
        'task_cron' => ['0', '0', '1', '1', '*'],
        'device' => Device::factory(1)->create()->map(fn ($device) => ['id' => $device->id])->all(),
        'category' => Category::factory(1)->create()->map(fn ($category) => ['id' => $category->id])->all(),
        'tag' => Tag::factory(1)->create()->map(fn ($tag) => ['id' => $tag->id])->all(),
        'task_email_notify' => 0,
        'download_report_notify' => 0,
        'verbose_download_report_notify' => 0,
    ], $overrides);
}

test('rejects an unsupported task command on create', function (string $command) {
    $this->json('post', '/api/tasks', taskCommandValidationPayload($command))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['task_command']);

    expect(Task::where('task_desc', 'task command validation')->count())->toBe(0);
})->with('unsupported task commands');

test('rejects an unsupported task command on validate task', function (string $command) {
    $this->json('post', '/api/tasks/validate-task', taskCommandValidationPayload($command))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['task_command']);
})->with('unsupported task commands');

test('rejects an unsupported task command on update', function (string $command) {
    $task = Task::factory()->create(['task_command' => 'rconfig:download-device']);

    $this->json('patch', '/api/tasks/' . $task->id, taskCommandValidationPayload($command, ['id' => $task->id]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['task_command']);

    expect($task->fresh()->task_command)->toBe('rconfig:download-device');
})->with('unsupported task commands');

test('rejects an unsupported task command on the token api create route', function () {
    User::factory()->create();
    $token = RestApiToken::factory()->create();

    $this->withHeaders(['apitoken' => $token->api_token])
        ->postJson('/api/v1/tasks', taskCommandValidationPayload('rconfig:download-device;id'))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['task_command']);
});

test('accepts every supported task command on create and update', function (string $command) {
    $created = $this->json('post', '/api/tasks', taskCommandValidationPayload($command))
        ->assertStatus(200);

    $taskId = $created->json('data.id');
    expect(Task::find($taskId)->task_command)->toBe($command);

    $this->json('patch', '/api/tasks/' . $taskId, taskCommandValidationPayload($command, ['id' => $taskId]))
        ->assertStatus(200);
})->with([
    'rconfig:download-device',
    'rconfig:download-category',
    'rconfig:download-tag',
    'rconfig:download-task',
]);
