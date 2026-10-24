<?php

use Illuminate\Support\Facades\File;

/**
 * Horizon's log moved out of storage/logs to /var/log, so the log viewer
 * config is published only to list it. These tests keep the stub and the
 * viewer pointing at the same file, and the published config otherwise equal
 * to the package default, so nothing else drifts unnoticed.
 */
const HORIZON_LOG = '/var/log/rconfig-core-horizon.log';

test('the log viewer lists the Horizon log and its rotated copies', function () {
    expect(config('log-viewer.include_files'))->toHaveKey(HORIZON_LOG . '*')
        ->and(config('log-viewer.include_files')[HORIZON_LOG . '*'])->toBe('Horizon');
});

test('the Supervisor stub writes Horizon output to the file the log viewer lists', function () {
    expect(File::get(base_path('horizon_supervisor.ini')))
        ->toContain("\nstdout_logfile=" . HORIZON_LOG . "\n")
        ->not->toContain('storage/logs/horizon.log');
});

test('the published log viewer config only adds the Horizon log to the package default', function () {
    $published = require config_path('log-viewer.php');
    $default = require base_path('vendor/opcodesio/log-viewer/config/log-viewer.php');

    unset($published['include_files'][HORIZON_LOG . '*']);

    expect($published)->toEqual($default);
});
