<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\Telnet\Connect as TelnetConnect;
use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;
use Tests\Unit\Connections\DeviceParamsBuilder;

/**
 * @var array<int, string>
 */
const DEPRECATED_KEYS = ['linebreak', 'pagerPrompt', 'pagerPromptCmd', 'hpAnyKeyPrmpt'];

/**
 * @param  callable(): void  $callback
 * @return array<int, string>
 */
function phpErrorsWhile(callable $callback): array
{
    $errors = [];

    set_error_handler(function (int $errno, string $errstr) use (&$errors): bool {
        $errors[] = $errstr;

        return true;
    }, E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);

    try {
        $callback();
    } finally {
        restore_error_handler();
    }

    return $errors;
}

test('ssh connect no longer declares the deprecated properties', function () {
    foreach (DEPRECATED_KEYS as $key) {
        expect(property_exists(SshConnect::class, $key))->toBeFalse("SSH Connect still declares the deprecated {$key} property.");
    }
});

test('telnet connect no longer declares the deprecated properties', function () {
    foreach (DEPRECATED_KEYS as $key) {
        expect(property_exists(TelnetConnect::class, $key))->toBeFalse("Telnet Connect still declares the deprecated {$key} property.");
    }
});

test('telnet read no longer declares the deprecated pager prompt', function () {
    expect(property_exists(TelnetRead::class, 'pagerPrompt'))->toBeFalse();
});

test('ssh connect builds without warnings when a template omits the deprecated keys', function () {
    $params = DeviceParamsBuilder::forSsh([
        'config' => ['linebreak' => null, 'pagerPrompt' => null, 'pagerPromptCmd' => null],
        'auth' => ['hpAnyKeyPrmpt' => null],
    ]);

    $errors = phpErrorsWhile(function () use ($params): void {
        new SshConnect($params, false);
    });

    expect($errors)->toBe([]);
});

test('telnet connect builds without warnings when a template omits the deprecated keys', function () {
    $params = DeviceParamsBuilder::forTelnet([
        'config' => ['linebreak' => null, 'pagerPrompt' => null, 'pagerPromptCmd' => null],
        'auth' => ['hpAnyKeyPrmpt' => null],
    ]);

    $errors = phpErrorsWhile(function () use ($params): void {
        new TelnetConnect($params, false);
    });

    expect($errors)->toBe([]);
});

test('ssh connect builds without warnings when a template still carries the deprecated keys', function () {
    $params = DeviceParamsBuilder::forSsh([
        'config' => ['linebreak' => 'n', 'pagerPrompt' => '--More--', 'pagerPromptCmd' => ' '],
        'auth' => ['hpAnyKeyPrmpt' => 'Press any key to continue'],
    ]);

    $errors = phpErrorsWhile(function () use ($params): void {
        new SshConnect($params, false);
    });

    expect($errors)->toBe([]);
});

test('telnet connect builds without warnings when a template still carries the deprecated keys', function () {
    $params = DeviceParamsBuilder::forTelnet([
        'config' => ['linebreak' => 'n', 'pagerPrompt' => '--More--', 'pagerPromptCmd' => ' '],
        'auth' => ['hpAnyKeyPrmpt' => 'Press any key to continue'],
    ]);

    $errors = phpErrorsWhile(function () use ($params): void {
        new TelnetConnect($params, false);
    });

    expect($errors)->toBe([]);
});
