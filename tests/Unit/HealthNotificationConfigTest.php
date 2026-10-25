<?php

/**
 * health:check sends its failure notification before it stores results, so a
 * missing mail address crashed every run and the dashboard kept showing the
 * last stored batch forever. Notifications must stay off unless both the
 * recipient and the sender are valid addresses.
 */
function healthNotificationsEnabledWith(?string $to, ?string $from): bool
{
    $original = ['HEALTH_MAIL_TO' => getenv('HEALTH_MAIL_TO'), 'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS')];

    setHealthTestEnv('HEALTH_MAIL_TO', $to);
    setHealthTestEnv('MAIL_FROM_ADDRESS', $from);

    try {
        return (require config_path('health.php'))['notifications']['enabled'];
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

test('health notifications are off when no addresses are set', function () {
    expect(healthNotificationsEnabledWith(null, null))->toBeFalse();
});

test('health notifications are off when the sender is the literal null', function () {
    expect(healthNotificationsEnabledWith('ops@example.com', 'null'))->toBeFalse();
});

test('health notifications are off when either address is empty or invalid', function (?string $to, ?string $from) {
    expect(healthNotificationsEnabledWith($to, $from))->toBeFalse();
})->with([
    'empty recipient' => ['', 'rconfig@example.com'],
    'missing recipient' => [null, 'rconfig@example.com'],
    'empty sender' => ['ops@example.com', ''],
    'invalid recipient' => ['not-an-address', 'rconfig@example.com'],
]);

test('health notifications are on when both addresses are valid', function () {
    expect(healthNotificationsEnabledWith('ops@example.com', 'rconfig@example.com'))->toBeTrue();
});

test('the health mail recipient has no placeholder default', function () {
    expect(file_get_contents(config_path('health.php')))->not->toContain('your@example.com');
});
