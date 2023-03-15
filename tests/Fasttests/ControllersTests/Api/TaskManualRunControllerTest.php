<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\MailTaskCompleteNotification;
use Artisan;
use Carbon\Carbon;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use ReflectionClass;
use Tests\TestCase;

class TaskManualRunControllerTest extends TestCase
{
    protected $user;
    protected $user2;
    protected $report_data;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->user2 = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
        $this->report_data = collect();
        $this->report_data->report_id = (string) Str::uuid();
        $this->report_data->task_type = 'Task Download Report';
        $this->report_data->task = '123';
        $this->report_data->start_time = Carbon::now();
        $this->report_data->end_time = Carbon::now();
        $this->report_data->file_name = $this->report_data->report_id . '.html';
        $this->report_data->report_path = report_path() . $this->report_data->file_name;

        Redis::flushall();
    }

    /** @test */
    public function can_run_a_download_device_task_manually_and_notification_sent()
    {
        Notification::fake();
        Notification::assertNothingSent();

        $this->assertDatabaseHas('tasks', [
            'id' => 555555,
            'task_email_notify' => '1',
        ]);

        $response = $this->json('post', '/api/tasks/run-manual-task', ['id' => '555555']);

        $response->assertJson(
            ['message' => 'TaskDownloadRun task pushed to queues successfully.']
        );
        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'task_id' => 555555,
            'type' => 'rconfig:download-device',

        ]);
        $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
            'task_id' => 555555,
            'meta' => 'Task started',
        ]);
        $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
            'task_id' => 555555,
            'meta' => 'Task finished',
        ]);

        Notification::assertSentTo(
            $this->user,
            \App\Notifications\MailTaskRunNotification::class,
            function ($notification, $channels) {
                // dd($notification->task);
                $property = $this->getPrivateProperty('\App\Notifications\MailTaskRunNotification', 'task');
                return $property->getValue($notification)['id'] === 555555;
            }
        );
    }

    /**
     * getPrivateProperty
     *
     * @author	Joe Sexton <joe@webtipblog.com>
     *
     * @param  string  $className
     * @param  string  $propertyName
     * @return	ReflectionProperty
     */
    public function getPrivateProperty($className, $propertyName)
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    /** @test */
    public function run_manual_task_test_backup_run_fails_with_fake_id()
    {
        Queue::fake();
        Queue::assertNothingPushed();

        $response = $this->post('/api/tasks/run-manual-task', [
            'id' => '123456',
        ]);

        $response->assertJson(
            ['message' => 'No query results for model [App\\Models\\Task] 123456']
        );

        Queue::assertNotPushed(BackupRun::class);
        $response->assertStatus(422);
    }

    /** @test */
    public function test_task_logging_for_downloads()
    {
        config(['queue.default' => 'redis']);

        $response = $this->json('post', '/api/tasks/run-manual-task', ['id' => '555555']);

        $this->assertDatabaseHas('tasks', [
            'id' => 555555,
        ]);

        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'task_id' => 555555,
        ]);
        $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
            'task_id' => 555555,
        ]);

        $response->assertJson(
            ['message' => 'TaskDownloadRun task pushed to queues successfully.']
        );

        config(['queue.default' => 'sync']);
    }


    // functions below used from https://github.com/laravel/horizon/tree/4.x/tests/Slowtests for testing queues
    protected function work($times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->worker()->runNextJob(
                'redis',
                'default',
                $this->workerOptions()
            );
        }
    }

    protected function worker()
    {
        return app('queue.worker');
    }

    protected function workerOptions()
    {
        return tap(new WorkerOptions, function ($options) {
            $options->sleep = 0;
            $options->maxTries = 1;
        });
    }

    /** @test */
    public function task_complete_notification_job_sent()
    {
        Queue::fake();

        dispatch(new \App\Jobs\TaskCompleteNotificationJob($this->report_data));
        Queue::assertPushed(\App\Jobs\TaskCompleteNotificationJob::class);
    }

    /** @test */
    public function mail_task_complete_notification_sent_to_all_users()
    {
        Notification::fake();
        Notification::assertNothingSent();

        Notification::send(User::allUsersAndRecipients(), new MailTaskCompleteNotification($this->report_data));

        Notification::assertSentTo(
            $this->user,
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $this->user2,
            MailTaskCompleteNotification::class
        );
    }

    /** @test */
    public function mail_task_complete_notification_sent_to_all_users_and_recipients()
    {
        Notification::fake();
        Notification::assertNothingSent();

        Setting::where('id', 1)->update(['mail_to_email' => 'stephenstack@gmail.com; alan@rconfig.com; helpdesk@rconfig.com']);

        $users = User::allUsersAndRecipients();

        Notification::send($users, new MailTaskCompleteNotification($this->report_data));

        Notification::assertSentTo(
            $this->user,
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $this->user2,
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $users->get(3),
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $users->get(4),
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $users->get(5),
            MailTaskCompleteNotification::class
        );
    }

    /** @test */
    public function mail_task_complete_notification_sent_to_all_users_and_recipients_is_empty()
    {
        Notification::fake();
        Notification::assertNothingSent();

        Setting::where('id', 1)->update(['mail_to_email' => '']);
        $users = User::allUsersAndRecipients();

        Notification::send($users, new MailTaskCompleteNotification($this->report_data));

        Notification::assertSentTo(
            $this->user,
            MailTaskCompleteNotification::class
        );
        Notification::assertSentTo(
            $this->user2,
            MailTaskCompleteNotification::class
        );
    }

    /**
     * Tear down the test case.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Redis::flushall();
    }
}
