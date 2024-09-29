<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TasksControllerTest extends TestCase
{
    protected $user;

    protected $validationTestArr;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');

        $this->validationTestArr = [
            'task_name' => '',
            'task_desc' => '',
            'task_command' => '',
            'category' => '',
            'device' => null,
            'task_tags' => null,
            'task_cron' => '',
            'task_email_notify' => 0,
            'download_report_notify' => true,
            'verbose_download_report_notify' => false,
            'is_system' => 0,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    public function test_delete_task_and_a_test_relationship()
    {
        $task = \App\Models\Task::factory()->make();
        $task->task_cron = ['0', '0', '1', '1', '*'];
        $task->task_name = $task->task_name . '-' . rand(1000, 10000);

        $tags = \App\Models\Tag::factory(3)->create();
        $response = $this->json('post', '/api/tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => ['0', '0', '1', '1', '*'],
            'category' => $task->category,
            'device' => ['1', '2'],
            'task_tags' => $tags,
            'task_email_notify' => $task->task_email_notify,
            'download_report_notify' => $task->download_report_notify,
            'verbose_download_report_notify' => $task->verbose_download_report_notify,
            'is_system' => $task->is_system,
        ]);
        $response->assertStatus(200);
 
        $latestTaskId = $response->json()['data']['id'];

        $cronPattern = $this->_getCronPattern('0', '0', '1', '1', '*');

        $this->assertDatabaseHas('tag_task', [
            'task_id' => $latestTaskId,
            'tag_id' => $tags->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => $cronPattern,
        ]);

        $this->delete('/api/tasks/' . $latestTaskId);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

        $this->assertDatabaseMissing('tag_task', [
            'task_id' => $latestTaskId,
            'tag_id' => $tags->pluck('id')->first(),
        ]);
    }

    public function test_a_task_requires_a_name_and_desc_and_command_and_cron()
    {
        $response = $this->json('post', '/api/tasks', ['task_name' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_command', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_a_cron_must_be_a_full_array()
    {
        $response = $this->json('post', '/api/tasks', ['task_cron' => ['*', '*', '*']]);

        $response->assertJson(['errors' => true]);
        $response->assertJsonFragment(['task_cron' => ['The cron format is incorrect.']]);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_a_cron_is_a_full_array()
    {
        $response = $this->json('post', '/api/tasks', ['task_cron' => ['*', '*', '*', '*', '*']]);

        $response->assertJsonMissing(['task_cron', $response['errors']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_device_passes()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-device';
        $this->validationTestArr['device'] = [
            [
                'id' => 1001,
                'device_name' => 'router1',
            ],
            [
                'id' => 1002,
                'device_name' => 'router1',
            ],
        ];
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $response->assertJsonMissing(['device', $response['errors']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_category_passes()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-category';
        $this->validationTestArr['category'] = [
            [
                'id' => 1001,
                'categoryName' => 'router1',
            ],
            [
                'id' => 1002,
                'categoryName' => 'router1',
            ],
        ];
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $response->assertJsonMissing(['category', $response['errors']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_task_tags_passes()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-tag';
        $this->validationTestArr['task_tags'] = [
            [
                'id' => 1001,
                'tag_name' => 'router1',
            ],
            [
                'id' => 1002,
                'tag_name' => 'router1',
            ],
        ];
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $response->assertJsonMissing(['task_tags', $response['errors']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_task_tags_fails_download()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-tag';
        $this->validationTestArr['task_tags'] = null;
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $this->assertArrayNotHasKey('device', $response['errors']);
        $this->assertArrayNotHasKey('category', $response['errors']);
        $response->assertJsonFragment(['task_tags' => ['The Tags - Config Downloads task must have tags associated with it. Please select at least one tag.']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_device_fails_download()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-device';
        $this->validationTestArr['device'] = null;
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $this->assertArrayNotHasKey('category', $response['errors']);
        $this->assertArrayNotHasKey('task_tags', $response['errors']);
        $response->assertJsonFragment(['device' => ['The Devices - Config Downloads task type must have devices associated with it. Please select at least one device.']]);
        $response->assertStatus(422);
    }

    public function test_check_custom_validators_category_fails_download()
    {
        $this->validationTestArr['task_command'] = 'rconfig:download-category';
        $this->validationTestArr['category'] = null;
        $response = $this->json('post', '/api/tasks/validate-task', $this->validationTestArr);

        $this->assertArrayHasKey('task_name', $response['errors']);
        $this->assertArrayHasKey('task_desc', $response['errors']);
        $this->assertArrayHasKey('task_cron', $response['errors']);
        $this->assertArrayNotHasKey('device', $response['errors']);
        $this->assertArrayNotHasKey('task_tags', $response['errors']);
        $response->assertJsonFragment(['category' => ['The Categories - Config Downloads task must have categories associated with it. Please select at least one category.']]);
        $response->assertStatus(422);
    }


    public function test_show_single_task()
    {
        $task = \App\Models\Task::factory()->create();
        $response = $this->get('/api/tasks/' . $task->id);

        $response->assertJson(['task_name' => $task->task_name]);
        $response->assertJson(['task_desc' => $task->task_desc]);
    }

    public function test_get_all_tasks()
    {
        $task = \App\Models\Task::factory(100)->create();
        $response = $this->get('/api/tasks?page=1&perPage=100');
        $this->assertEquals(100, count($response->json()['data']));
        $this->assertStringContainsString('Every Sunday at 12:00am', json_decode($response->getContent())->data[0]->cron_plain);
        $response->assertStatus(200);
    }

    public function test_create_task_with_real_props_and_transform_some_props()
    {
        $task = [
            'task_name' => 'avsasvascasc',
            'task_desc' => 'asvsavascasc',
            'task_command' => 'rconfig:download-device',
            'category' => '',
            'device' => [
                0 => [
                    'id' => 1004,
                    'device_name' => 'router4',
                ],
                1 => [
                    'id' => 1005,
                    'device_name' => 'router5',
                ],
                2 => [
                    'id' => 1006,
                    'device_name' => 'router6',
                ],
                3 => [
                    'id' => 1008,
                    'device_name' => 'router8',
                ],
            ],
            'task_tags' => null,
            'task_cron' => [
                0 => '0',
                1 => '0',
                2 => '1',
                3 => '1',
                4 => '*',
            ],
            'task_email_notify' => true,
            'download_report_notify' => true,
            'verbose_download_report_notify' => true,
            'is_system' => 0,
            'created_at' => null,
            'updated_at' => null,
        ];

        $response = $this->json('post', '/api/tasks', $task);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task['task_name'],
            'task_desc' => $task['task_desc'],
            'task_command' => $task['task_command'],
            'task_email_notify' => 1,
            'download_report_notify' => 1,
            'verbose_download_report_notify' => 1,
        ]);
        $this->assertDatabaseHas('device_task', [
            'device_id' => 1004,
            'task_id' => json_decode($response->getContent())->data->id,
        ]);
        $this->assertDatabaseHas('device_task', [
            'device_id' => 1005,
            'task_id' => json_decode($response->getContent())->data->id,
        ]);
    }

    public function test_create_task_with_real_props_and_transform_some_props_and_verify_device_relationship()
    {
        $task = [
            'task_name' => 'avsasvascasc123123',
            'task_desc' => 'asvsavascasc123123',
            'task_command' => 'rconfig:download-device',
            'category' => '',
            'device' => [
                0 => [
                    'id' => 1004,
                    'device_name' => 'router4',
                ],
                1 => [
                    'id' => 1005,
                    'device_name' => 'router5',
                ],
                2 => [
                    'id' => 1006,
                    'device_name' => 'router6',
                ],
                3 => [
                    'id' => 1008,
                    'device_name' => 'router8',
                ],
            ],
            'task_tags' => null,
            'task_cron' => [
                0 => '0',
                1 => '0',
                2 => '1',
                3 => '1',
                4 => '*',
            ],
            'task_email_notify' => true,
            'download_report_notify' => true,
            'verbose_download_report_notify' => true,
            'is_system' => 0,
            'created_at' => null,
            'updated_at' => null,
        ];

        $response = $this->json('post', '/api/tasks', $task);

        $response = $this->json('get', '/api/tasks/' . json_decode($response->getContent())->data->id);
        $this->assertCount(4, json_decode($response->getContent())->device);
    }

    public function test_create_task()
    {
        $task = \App\Models\Task::factory()->make();

        $response = $this->json('post', '/api/tasks', $task->toArray());
        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
        ]);

        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'name' => $task->task_name,
            'type' => $task->task_command,
        ]);
    }

    public function test_edit_task()
    {
        $task = \App\Models\Task::factory()->create();

        $response = $this->json('patch', '/api/tasks/' . $task->id, [
            'task_name' => 'a-new-task-name',
            'task_desc' => 'this is a new task description',
            'task_command' => $task->task_command,
            'task_cron' => ['0', '0', '1', '1', '*'],
            'category' => $task->category,
            'device' => ['1', '2'],
            'task_tags' => ['1', '2'],
            'task_email_notify' => $task->task_email_notify,
            'download_report_notify' => $task->download_report_notify,
            'verbose_download_report_notify' => $task->verbose_download_report_notify,
            'is_system' => $task->is_system,
            'editId' => $task->id,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tasks', [
            'task_name' => 'a-new-task-name',
            'task_desc' => 'this is a new task description',
            'task_command' => 'rconfig:download-device',
        ]);

        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'name' => 'a-new-task-name',
            'type' => 'rconfig:download-device',
        ]);
    }

    public function test_delete_task()
    {
        $task = \App\Models\Task::factory()->create();

        $this->delete('/api/tasks/' . $task->id);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_test_tag_relationship()
    {
        $task = \App\Models\Task::factory()->make(['task_command' => 'rconfig:download-tag']);
        $task->task_cron = ['0', '0', '1', '1', '*'];
        $task->task_name = $task->task_name . '-' . rand(1000, 10000);

        $tags = \App\Models\Tag::factory(3)->create();
        $response = $this->json('post', '/api/tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => ['0', '0', '1', '1', '*'],
            'category' => $task->category,
            'device' => null,
            'task_tags' => $tags,
            'task_email_notify' => $task->task_email_notify,
            'download_report_notify' => $task->download_report_notify,
            'verbose_download_report_notify' => $task->verbose_download_report_notify,
            'is_system' => $task->is_system,
        ]);
        $latestTaskId = $response->json()['data']['id'];

        $cronPattern = $this->_getCronPattern('0', '0', '1', '1', '*');

        $this->assertDatabaseHas('tag_task', [
            'task_id' => $latestTaskId,
            'tag_id' => $tags->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => $cronPattern,
        ]);
    }

    public function test_test_device_relationship()
    {
        $task = \App\Models\Task::factory()->make();
        $task->task_cron = ['0', '0', '1', '1', '*'];
        $task->task_name = $task->task_name . '-' . rand(1000, 10000);

        $devices = \App\Models\Device::factory(3)->create();
        $response = $this->json('post', '/api/tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => ['0', '0', '1', '1', '*'],
            'category' => $task->category,
            'device' => $devices,
            'task_tags' => $task->task_tags,
            'task_email_notify' => $task->task_email_notify,
            'download_report_notify' => $task->download_report_notify,
            'verbose_download_report_notify' => $task->verbose_download_report_notify,
            'is_system' => $task->is_system,
        ]);
        $latestTaskId = $response->json()['data']['id'];

        $cronPattern = $this->_getCronPattern('0', '0', '1', '1', '*');

        $this->assertDatabaseHas('device_task', [
            'task_id' => $latestTaskId,
            'device_id' => $devices->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => $cronPattern,
        ]);
    }

    public function test_test_category_relationship()
    {
        $task = \App\Models\Task::factory()->make();
        $task->task_cron = ['0', '0', '1', '1', '*'];
        $task->task_name = $task->task_name . '-' . rand(1000, 10000);

        $categories = \App\Models\Category::factory(3)->create();
        $response = $this->json('post', '/api/tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => ['0', '0', '1', '1', '*'],
            'category' => $categories,
            'device' => ['1', '2'],
            'task_tags' => $task->task_tags,
            'task_email_notify' => $task->task_email_notify,
            'download_report_notify' => $task->download_report_notify,
            'verbose_download_report_notify' => $task->verbose_download_report_notify,
            'is_system' => $task->is_system,
        ]);
        $latestTaskId = $response->json()['data']['id'];

        $cronPattern = $this->_getCronPattern('0', '0', '1', '1', '*');

        $this->assertDatabaseHas('category_task', [
            'task_id' => $latestTaskId,
            'category_id' => $categories->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
            'task_cron' => $cronPattern,
        ]);
    }

    public function test_task_finished_relationship()
    {
        $task = \App\Models\Task::factory()->create();
        $this->post('/api/tasks', $task->toArray());

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'task_name' => $task->task_name,
            'task_desc' => $task->task_desc,
            'task_command' => $task->task_command,
        ]);

        $monitored_scheduled_tasks =
            [
                'task_id' => $task->id,
                'name' => 'rconfig:download-task ' . $task->id,
                'type' => 'command',
                'cron_expression' => '0 0 1 1 *',
                'timezone' => 'Europe/Dublin',
                'ping_url' => null,
                'last_started_at' => '2022-05-27 08:44:05',
                'last_finished_at' => '2022-05-27 08:44:05',
                'last_failed_at' => null,
                'last_skipped_at' => null,
                'created_at' => '2022-05-26 21:40:05',
                'updated_at' => '2022-05-27 08:44:05',
            ];

        DB::table('monitored_scheduled_tasks')->insert($monitored_scheduled_tasks);

        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'name' => $monitored_scheduled_tasks['name'],
            'last_started_at' => $monitored_scheduled_tasks['last_started_at'],
            'last_finished_at' => $monitored_scheduled_tasks['last_finished_at'],
        ]);

        $response = $this->get('/api/tasks/' . $task->id);

        $response->assertJson(['task_name' => $task->task_name]);
        $response->assertJson(['task_desc' => $task->task_desc]);
        $response->assertJsonFragment(['last_finished_at' => '2022-05-27 08:44:05']);
    }

    private function _getCronPattern($minute, $hour, $day, $month, $weekday)
    {
        return $minute . ' ' . $hour . ' ' . $day . ' ' . $month . ' ' . $weekday . ' ';
    }
}
