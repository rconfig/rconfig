<?php

use Illuminate\Support\Facades\File;

/**
 * Horizon writes its own log to storage/logs/horizon.log, as the web user,
 * which the package default *.log pattern already lists. These tests keep the
 * stub and the viewer pointing at the same file, and the published config
 * equal to the package default, so nothing else drifts unnoticed.
 */
const HORIZON_LOG = 'horizon.log';

test('the log viewer lists the Horizon log in storage/logs and does not exclude it', function () {
    $relative = fn (array $patterns): array => collect($patterns)
        ->map(fn (string $alias, int|string $pattern): string => is_int($pattern) ? $alias : $pattern)
        ->reject(fn (string $pattern): bool => str_starts_with($pattern, '/'))
        ->filter(fn (string $pattern): bool => fnmatch($pattern, HORIZON_LOG))
        ->values()
        ->all();

    expect($relative(config('log-viewer.include_files')))->not->toBeEmpty()
        ->and($relative(config('log-viewer.exclude_files')))->toBe([]);
});

test('the Supervisor stub has Horizon write the file the log viewer lists', function () {
    expect(File::get(base_path('horizon_supervisor.ini')))
        ->toContain('>> RCONFIG_APP_DIR/storage/logs/' . HORIZON_LOG . ' 2>&1')
        ->not->toContain('/var/log/rconfig-core-horizon.log');
});

test('the published log viewer config is the package default with no /var/log Horizon entry', function () {
    $published = require config_path('log-viewer.php');
    $default = require base_path('vendor/opcodesio/log-viewer/config/log-viewer.php');

    expect($published['include_files'])->not->toHaveKey('/var/log/rconfig-core-horizon.log*')
        ->and($published)->toEqual($default);
});
