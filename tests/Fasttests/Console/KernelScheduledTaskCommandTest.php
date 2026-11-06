<?php

use App\Models\Task;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * Rebuild the schedule so Kernel::schedule() reads the tasks table as the test left it.
 *
 * @return Collection<int, Event>
 */
function scheduledTaskCommandEvents(): Collection
{
    app()->forgetInstance(Schedule::class);

    return collect(app(Schedule::class)->events());
}

/**
 * @return Collection<int, Event>
 */
function scheduledEventsForTask(int $taskId): Collection
{
    return scheduledTaskCommandEvents()->filter(
        fn (Event $event) => preg_match('/(^|\\s)' . $taskId . '(\\s|$)/', (string) $event->command) === 1
    );
}

test('schedules every supported task command as the download task command with the task id', function (string $command) {
    $task = Task::factory()->create([
        'task_command' => $command,
        'task_cron' => '15 3 * * *',
        'is_paused' => 0,
    ]);

    $events = scheduledEventsForTask($task->id);

    expect($events)->toHaveCount(1);
    expect($events->first()->command)->toEndWith(' rconfig:download-task ' . $task->id);
    expect($events->first()->expression)->toBe('15 3 * * *');
})->with([
    'rconfig:download-device',
    'rconfig:download-category',
    'rconfig:download-tag',
    'rconfig:download-task',
]);

test('does not schedule a stored task with an unsupported task command', function (string $command) {
    Log::spy();

    $task = Task::factory()->create([
        'task_command' => $command,
        'task_cron' => '* * * * *',
        'is_paused' => 0,
    ]);

    expect(scheduledEventsForTask($task->id))->toBeEmpty();
    expect(scheduledTaskCommandEvents()->filter(
        fn (Event $event) => str_contains((string) $event->command, 'scheduled-task-marker') || str_contains((string) $event->command, ' about')
    ))->toBeEmpty();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $context['task_id'] === $task->id)
        ->atLeast()->once();
})->with([
    'command with appended shell text' => ['rconfig:download-device;echo scheduled-task-marker'],
    'other artisan command' => ['about'],
    'unknown command with argument' => ['rconfig:download-device scheduled-task-marker'],
]);

test('does not schedule a paused task', function () {
    $task = Task::factory()->create([
        'task_command' => 'rconfig:download-device',
        'is_paused' => 1,
    ]);

    expect(scheduledEventsForTask($task->id))->toBeEmpty();
});

test('pause migration pauses only tasks with an unsupported task command', function () {
    Log::spy();

    $supported = Task::factory()->create(['task_command' => 'rconfig:download-tag', 'is_paused' => 0]);
    $unsupported = Task::factory()->create(['task_command' => 'rconfig:download-tag;id', 'is_paused' => 0]);
    DB::table('tasks')->where('id', $unsupported->id)->update(['task_command' => 'rconfig:download-tag;id']);

    $migration = require database_path('migrations/2026_11_06_161252_pause_tasks_with_unsupported_task_command.php');
    $migration->up();

    expect((bool) $supported->fresh()->is_paused)->toBeFalse();
    expect((bool) $unsupported->fresh()->is_paused)->toBeTrue();
    expect(Task::find($unsupported->id))->not->toBeNull();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context) => $context['task_id'] === $unsupported->id)
        ->once();
});
