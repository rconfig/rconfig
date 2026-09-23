<?php

use App\Jobs\TaskDownloadRun;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('a crafted task command is rejected and never reaches artisan', function () {
    Artisan::spy();

    // Mirrors the disclosed queue-poisoning payload: attacker-chosen command + option injection via id.
    $job = new TaskDownloadRun([
        'task_command' => 'tinker',
        'id' => '--execute="file_put_contents(\'/tmp/pwn\', shell_exec(\'id\'))"',
    ]);

    $job->handle();

    Artisan::shouldNotHaveReceived('call');
});

test('a non-numeric task id is rejected and never reaches artisan', function () {
    Artisan::spy();

    $job = new TaskDownloadRun([
        'task_command' => 'rconfig:download-task',
        'id' => '5 --execute="phpinfo()"',
    ]);

    $job->handle();

    Artisan::shouldNotHaveReceived('call');
});

test('a legitimate download task runs the fixed command with a bound integer id', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('rconfig:download-task', ['taskid' => [7]]);
    Artisan::shouldReceive('output')
        ->andReturn('done');

    // Legacy stored commands normalise to rconfig:download-task, exactly as the controller and scheduler do.
    $job = new TaskDownloadRun([
        'task_command' => 'rconfig:download-device',
        'id' => '7',
    ]);

    $job->handle();
});
