<?php

use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function tasksApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns tasks', function () {
    Task::factory(3)->create();

    $this->withHeaders(tasksApiV1AuthHeader($this->token))
        ->getJson('/api/v1/tasks?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns task', function () {
    $task = Task::factory()->create();

    $this->withHeaders(tasksApiV1AuthHeader($this->token))
        ->getJson('/api/v1/tasks/' . $task->id)
        ->assertStatus(200)
        ->assertJsonFragment(['task_name' => $task->task_name]);
});

test('store creates task', function () {
    $tags = Tag::factory(3)->create();
    $taskName = 'rest-api-task-' . rand(100000, 999999);

    $response = $this->withHeaders(tasksApiV1AuthHeader($this->token))
        ->postJson('/api/v1/tasks', [
            'task_name' => $taskName,
            'task_desc' => 'Created via REST API test',
            'task_command' => 'rconfig:download-device',
            'task_cron' => ['0', '0', '1', '1', '*'],
            'device' => ['1', '2'],
            'tag' => $tags->toArray(),
            'task_email_notify' => true,
            'download_report_notify' => true,
            'verbose_download_report_notify' => true,
            'is_system' => 0,
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('tasks', ['task_name' => $taskName, 'task_cron' => '0 0 1 1 * ']);

    // Best-effort cleanup; the shared test DB can throw a transient lock wait
    // timeout when removing tag_task rows, which is not what this test asserts.
    try {
        $this->withHeaders(tasksApiV1AuthHeader($this->token))
            ->deleteJson('/api/v1/tasks/' . $response->json('data.id'));
    } catch (Throwable $e) {
        if (! str_contains($e->getMessage(), 'Lock wait timeout exceeded')) {
            throw $e;
        }
    }
});

test('store validation failure returns 422', function () {
    $this->withHeaders(tasksApiV1AuthHeader($this->token))
        ->postJson('/api/v1/tasks', ['task_name' => null])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['task_name', 'task_command', 'task_cron']);
});

test('destroy deletes task', function () {
    $task = Task::factory()->create();

    $this->withHeaders(tasksApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/tasks/' . $task->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

// Note: update (PATCH) is exercised by the SPA TasksControllerTest using
// UpdateTaskRequest; the token-authenticated route shares the same controller.
