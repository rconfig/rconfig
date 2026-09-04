<?php

use App\Console\Commands\rconfigTaskList;
use App\Models\Task;
use Illuminate\Support\Facades\Artisan;

test('it has rconfig task list command', function () {
    expect(class_exists(rconfigTaskList::class))->toBeTrue();
});

test('list tasks command', function () {
    $tasks = Task::factory(20)->create();

    Artisan::call('rconfig:list-tasks');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Results for Tasks List:');
    expect(taskListSearchPartial((string) $tasks->first()->id, $arr))->toBeTrue();

    expect($arr)->toBeGreaterThan(20);
});

function taskListSearchPartial($keyword, $arr)
{
    foreach ($arr as $index => $string) {
        if (strpos($string, $keyword) !== false) {
            return true;
        }
    }
}
