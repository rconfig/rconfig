<?php

use App\HealthChecks\Notifications\HealthAlertResultStore;
use Spatie\Health\ResultStores\EloquentHealthResultStore;

/**
 * Health alerts go through rConfig's notification channels (HealthAlertResultStore),
 * not spatie's own mail notification. Spatie's path read its addresses from .env,
 * which rConfig does not use for mail, and a missing address crashed every run.
 */
function healthConfigWithEnv(array $env): array
{
    $original = [];

    foreach ($env as $key => $value) {
        $original[$key] = getenv($key);
        setHealthTestEnv($key, $value);
    }

    try {
        return require config_path('health.php');
    } finally {
        foreach ($original as $key => $value) {
            setHealthTestEnv($key, $value === false ? null : $value);
        }
    }
}

function setHealthTestEnv(string $key, ?string $value): void
{
    if ($value === null) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);

        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

test('spatie health notifications stay off whatever the environment says', function () {
    $config = healthConfigWithEnv(['HEALTH_MAIL_TO' => 'ops@example.com', 'MAIL_FROM_ADDRESS' => 'rconfig@example.com']);

    expect($config['notifications'])->toBe(['enabled' => false]);
});

test('the alert store runs before the eloquent store saves the run', function () {
    $stores = collect(config('health.result_stores'))
        ->keyBy(fn (mixed $value, mixed $key) => is_array($value) ? $key : $value)
        ->keys()
        ->all();

    expect($stores)->toBe([HealthAlertResultStore::class, EloquentHealthResultStore::class]);
});

test('the health config has no placeholder or env-based mail addresses', function () {
    $contents = file_get_contents(config_path('health.php'));

    expect($contents)
        ->not->toContain('your@example.com')
        ->not->toContain('HEALTH_MAIL_TO')
        ->not->toContain("config('mail.");
});
