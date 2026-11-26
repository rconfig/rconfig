<?php

use App\Models\PurgeRun;
use App\Services\DataPurge\PurgeRunRecorder;

beforeEach(function () {
    $this->beginTransaction();
    $this->recorder = app(PurgeRunRecorder::class);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('start records a running run with its scope and options', function () {
    $run = $this->recorder->start('data_purge', ['table' => 'tracked_jobs'], ['dry_run' => true], true, 'purge', 'rconfig:data-purge');

    expect($run->fresh())
        ->status->toBe('running')
        ->dry_run->toBeTrue()
        ->mode->toBe('purge')
        ->source->toBe('rconfig:data-purge')
        ->scope->toBe(['table' => 'tracked_jobs'])
        ->summary->toBe(['options' => ['dry_run' => true]])
        ->finished_at->toBeNull();
});

test('finish stores the metrics and the final status', function () {
    $run = $this->recorder->start('data_purge', [], ['dry_run' => false]);

    $this->recorder->finish($run, ['total_checked' => 2, 'errors' => ['bad table']], 'completed_with_errors');

    expect($run->fresh())
        ->status->toBe('completed_with_errors')
        ->finished_at->not->toBeNull()
        ->and($run->fresh()->summary['metrics'])->toBe(['total_checked' => 2, 'errors' => ['bad table']])
        ->and($run->fresh()->summary['options'])->toBe(['dry_run' => false]);
});

test('fail stores a truncated error message', function () {
    $run = $this->recorder->start('data_purge');

    $this->recorder->fail($run, new RuntimeException(str_repeat('x', 5000)));

    expect($run->fresh()->status)->toBe('failed')
        ->and($run->fresh()->message)->toStartWith(str_repeat('x', 4000))
        ->and($run->fresh()->message)->toEndWith('[truncated 1000 chars]');
});

test('long lists in the metrics are capped', function () {
    $run = $this->recorder->start('data_purge');

    $this->recorder->finish($run, ['tables_processed' => range(1, 250)]);

    $stored = PurgeRun::find($run->id)->summary['metrics']['tables_processed'];

    expect($stored)->toHaveCount(201)
        ->and(end($stored))->toBe(['_truncated' => true, 'total_items' => 250]);
});
