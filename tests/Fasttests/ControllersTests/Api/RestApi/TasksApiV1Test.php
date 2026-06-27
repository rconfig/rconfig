<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class TasksApiV1Test extends TestCase
{
    use TolerantOfTransientDbLocks;

    protected RestApiToken $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    public function test_index_returns_tasks(): void
    {
        Task::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/tasks?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_task(): void
    {
        $task = Task::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/tasks/' . $task->id)
            ->assertStatus(200)
            ->assertJsonFragment(['task_name' => $task->task_name]);
    }

    public function test_store_creates_task(): void
    {
        $tags = Tag::factory(3)->create();
        $taskName = 'rest-api-task-' . rand(100000, 999999);

        $response = $this->withHeaders($this->authHeader())
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
            $this->withHeaders($this->authHeader())
                ->deleteJson('/api/v1/tasks/' . $response->json('data.id'));
        } catch (\Throwable $e) {
            if (! str_contains($e->getMessage(), 'Lock wait timeout exceeded')) {
                throw $e;
            }
        }
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/tasks', ['task_name' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task_name', 'task_command', 'task_cron']);
    }

    public function test_destroy_deletes_task(): void
    {
        $task = Task::factory()->create();

        $this->deleteJsonTolerant('/api/v1/tasks/' . $task->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // Note: update (PATCH) is exercised by the SPA TasksControllerTest using
    // UpdateTaskRequest; the token-authenticated route shares the same controller.
}
