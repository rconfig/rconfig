<?php

use App\Jobs\TaskCompleteNotificationJob;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MailTaskCompleteNotification;
use Carbon\Carbon;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tests\Fasttests\ControllersTests\Api\BackupRun;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->actingAs($this->user);
    $this->report_data = collect();
    $this->report_data->report_id = (string) Str::uuid();
    $this->report_data->task_type = 'Task Download Report';
    $this->report_data->task = '123';
    $this->report_data->start_time = Carbon::now();
    $this->report_data->end_time = Carbon::now();
    $this->report_data->file_name = $this->report_data->report_id . '.html';
    $this->report_data->report_path = report_path() . $this->report_data->file_name;

    Redis::flushall();
});

test('can run a download device task manually and notification sent', function () {
    Notification::fake();
    Notification::assertNothingSent();

    $task = Task::factory()->create(['task_email_notify' => 1]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'task_email_notify' => '1',
    ]);

    $response = $this->json('post', '/api/tasks/run-manual-task', ['id' => (string) $task->id]);

    $response->assertJson(
        ['message' => 'TaskDownloadRun task pushed to queues successfully.']
    );
    $this->assertDatabaseHas('monitored_scheduled_tasks', [
        'task_id' => $task->id,
        'type' => 'rconfig:download-device',

    ]);
    $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
        'task_id' => $task->id,
        'meta' => 'Task started',
    ]);
    $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
        'task_id' => $task->id,
        'meta' => 'Task finished',
    ]);
});

/**
 * getPrivateProperty
 *
 * Not currently called by any test in this file, kept for reference.
 *
 * @author	Joe Sexton <joe@webtipblog.com>
 *
 * @param  string  $className
 * @param  string  $propertyName
 * @return ReflectionProperty
 */
// function getPrivateProperty($className, $propertyName)
// {
//     $reflector = new ReflectionClass($className);
//     $property = $reflector->getProperty($propertyName);
//     $property->setAccessible(true);
//
//     return $property;
// }

test('run manual task test backup run fails with fake id', function () {
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
});

test('test task logging for downloads', function () {
    config(['queue.default' => 'redis']);

    $task = Task::factory()->create();

    $response = $this->json('post', '/api/tasks/run-manual-task', ['id' => (string) $task->id]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
    ]);

    $this->assertDatabaseHas('monitored_scheduled_tasks', [
        'task_id' => $task->id,
    ]);
    $this->assertDatabaseHas('monitored_scheduled_task_log_items', [
        'task_id' => $task->id,
    ]);

    $response->assertJson(
        ['message' => 'TaskDownloadRun task pushed to queues successfully.']
    );

    config(['queue.default' => 'sync']);
});

// functions below used from https://github.com/laravel/horizon/tree/4.x/tests/Slowtests for testing queues
// Not currently called by any test in this file, kept for reference.
// function work($times = 1)
// {
//     for ($i = 0; $i < $times; $i++) {
//         worker()->runNextJob(
//             'redis',
//             'default',
//             workerOptions()
//         );
//     }
// }

// function worker()
// {
//     return app('queue.worker');
// }

// function workerOptions()
// {
//     return tap(new WorkerOptions, function ($options) {
//         $options->sleep = 0;
//         $options->maxTries = 1;
//     });
// }

test('task complete notification job sent', function () {
    Queue::fake();

    dispatch(new TaskCompleteNotificationJob($this->report_data));
    Queue::assertPushed(TaskCompleteNotificationJob::class);
});

test('mail task complete notification sent to all users', function () {
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
});

test('mail task complete notification sent to all users and recipients', function () {
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
});

test('mail task complete notification sent to all users and recipients is empty', function () {
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
});

/**
 * Tear down the test case.
 */
afterEach(function () {
    $this->rollBackTransaction();

    Redis::flushall();
});
