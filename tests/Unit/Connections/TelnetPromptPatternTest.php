<?php

use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;

function buildPattern(string $prompt): string
{
    $reflection = new ReflectionClass(TelnetRead::class);
    $read = $reflection->newInstanceWithoutConstructor();

    return $reflection->getMethod('buildPromptPattern')->invoke($read, $prompt);
}

function matchesPrompt(string $prompt, string $buffer): bool
{
    $reflection = new ReflectionClass(TelnetRead::class);
    $read = $reflection->newInstanceWithoutConstructor();

    $reflection->getProperty('prompt')->setValue($read, $prompt);
    $reflection->getProperty('promptPattern')->setValue($read, buildPattern($prompt));
    $reflection->getProperty('data')->setValue($read, $buffer);

    return (bool) $reflection->getMethod('readToPrompt')->invoke($read);
}

test('prompt containing the delimiter still matches', function () {
    expect(matchesPrompt('admin@sw1/config#', "show run\r\nadmin@sw1/config#"))->toBeTrue();
});

test('mikrotik style prompt still matches', function () {
    $prompt = '[admin@MikroTik] /interface>';

    expect(matchesPrompt($prompt, "output\r\n[admin@MikroTik] /interface>"))->toBeTrue();
});

test('plain prompt still matches', function () {
    expect(matchesPrompt('sw1#', "show run\r\nsw1#"))->toBeTrue();
});

test('prompt must appear at the end of the buffer', function () {
    expect(matchesPrompt('sw1#', "sw1#\r\nshow running-config\r\n"))->toBeFalse();
});

test('buffer without the prompt does not match', function () {
    expect(matchesPrompt('admin@sw1/config#', "show run\r\nsomething else"))->toBeFalse();
});

test('prompt full of metacharacters matches as typed', function () {
    expect(matchesPrompt('sw1(config)#', "show run\r\nsw1(config)#"))->toBeTrue();
    expect(matchesPrompt('R1.core+#', "show run\r\nR1.core+#"))->toBeTrue();
    expect(matchesPrompt('sw1(config)#', "show run\r\nsw2(config)#"))->toBeFalse();
});

test('empty prompt still matches anything', function () {
    expect(matchesPrompt('', "show run\r\n"))->toBeTrue();
});

test('regex capable prompts are preserved', function () {
    expect(matchesPrompt('sw1[>#]', "show run\r\nsw1#"))->toBeTrue();
    expect(matchesPrompt('sw1[>#]', "show run\r\nsw1>"))->toBeTrue();
    expect(matchesPrompt('sw1[>#]', "show run\r\nsw1$"))->toBeFalse();
});

test('prompt that cannot compile falls back to a literal match', function () {
    $prompt = 'sw1(config';

    expect(buildPattern($prompt))->toBe('/' . preg_quote($prompt, '/') . '$/');
    expect(matchesPrompt($prompt, "show run\r\nsw1(config"))->toBeTrue();
});

test('every built pattern compiles', function () {
    $prompts = [
        'sw1#',
        'admin@sw1/config#',
        '[admin@MikroTik] /interface>',
        'sw1(config',
        'sw1[>#',
        'sw1*',
        '\\',
    ];

    foreach ($prompts as $prompt) {
        expect(preg_match(buildPattern($prompt), ''))->not->toBeFalse('Pattern built for prompt [' . $prompt . '] does not compile.');
    }
});
