<?php

use App\Services\Templates\TemplateReformatter;
use Symfony\Component\Yaml\Yaml;

beforeEach(function () {
    $this->oldFormatPath = base_path('tests/storage/templates/oldformat.yml');
    $this->newFormatPath = base_path('tests/storage/templates/newformat.yml');
    $this->inlineCommentsPath = base_path('tests/storage/templates/inlinecomments.yml');
    $this->vt100Path = base_path('tests/storage/templates/vt100.yml');

    $this->reformatter = new TemplateReformatter;
});

test('can instantiate template reformatter', function () {
    expect($this->reformatter)->toBeInstanceOf(TemplateReformatter::class);
});

test('detects already new format template', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Template file is already in the new format: ' . $this->newFormatPath);

    $this->reformatter->reformatTemplateFile($this->newFormatPath);
});

test('can determine template format correctly', function () {
    expect($this->reformatter->isNewFormat($this->oldFormatPath))->toBeFalse();
    expect($this->reformatter->isNewFormat($this->newFormatPath))->toBeTrue();
});

test('can convert old format to new format', function () {
    $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

    expect($result)->toBeString();
    expect($result)->not->toBeEmpty();
});

test('converted template has correct structure', function () {
    $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

    $this->assertStringContainsString('name: "SSH Private Key Template"', $result);
    $this->assertStringContainsString('# Unique name for this template', $result);
    $this->assertStringContainsString('# Port number for connection', $result);
    $this->assertStringContainsString('exitCmd: "quit"', $result);
    $this->assertStringContainsString('setTerminalDimensions: [260, 100000]', $result);
});

test('handles invalid file path', function () {
    $this->expectException(Exception::class);

    $this->reformatter->reformatTemplateFile(base_path('tests/storage/templates/does-not-exist.yml'));
});

test('reformatting template with inline comments does not break quotes', function () {
    $result = $this->reformatter->reformatTemplateFile($this->inlineCommentsPath);

    // The original inline comment text must not survive inside the value
    $this->assertStringNotContainsString('# Cisco IOS via TELNET without enable mode"', $result);
    $this->assertStringNotContainsString('# Disable CLI paging"', $result);

    // The name value is cleanly quoted and the rConfig comment sits outside it
    $this->assertStringContainsString('name: "Cisco IOS - TELNET - No Enable - test 2500"', $result);
    $this->assertStringContainsString('# Unique name for this template', $result);

    // Every value line must have a balanced number of double quotes (0 or 2)
    foreach (explode("\n", $result) as $line) {
        if (! preg_match('/^\s{2}[a-zA-Z]/', $line)) {
            continue;
        }

        expect(substr_count($line, '"') % 2)->toBe(0, "Line has unbalanced quotes: {$line}");
    }
});

test('unquoted and array values drop their inline comments', function () {
    $result = $this->reformatter->reformatTemplateFile($this->inlineCommentsPath);

    // Unquoted scalars keep their bare value with the original comment stripped
    $this->assertStringContainsString('protocol: telnet ', $result);
    $this->assertStringContainsString('port: 23 ', $result);
    $this->assertStringNotContainsString('protocol: "telnet', $result);

    // Array value is preserved without the trailing inline comment
    $this->assertStringContainsString('setWindowSize: [240, 2048]', $result);
    $this->assertStringNotContainsString('setWindowSize: "[240, 2048]', $result);
});

test('hash inside quoted value is preserved', function () {
    $template = "main:\n  name: \"Cisco #1 Core\"   # inline note\n  desc: \"edge\"\n";

    $result = $this->reformatter->reformatTemplate($template);

    $this->assertStringContainsString('name: "Cisco #1 Core"', $result);
    $this->assertStringNotContainsString('inline note', $result);
});

test('vt100 section survives a reformat', function () {
    $result = $this->reformatter->reformatTemplateFile($this->vt100Path);

    $parsed = Yaml::parse($result);

    expect($parsed)->toHaveKey('vt100');
    expect($parsed['vt100']['hasSplashScreen'])->toBe('on');
    expect($parsed['vt100']['hasSplashScreenEnterKey'])->toBe('off');
    expect($parsed['vt100']['splashScreenReadToText'])->toBe('Ctrl-Y');
    expect($parsed['vt100']['splashScreenSendControlCode'])->toBe('Y');

    // The vt100 keys must not leak into the preceding section
    $this->assertArrayNotHasKey('vt100', $parsed['options']);
    $this->assertArrayNotHasKey('hasSplashScreen', $parsed['options']);

    // And the section is documented like every other known section
    $this->assertStringContainsString('# Device shows a splash screen before login?', $result);
});

test('no section is lost during a reformat', function () {
    $before = Yaml::parse(file_get_contents($this->vt100Path));
    $after = Yaml::parse($this->reformatter->reformatTemplateFile($this->vt100Path));

    expect(array_keys($after))->toBe(array_keys($before));
});

test('nested sections are preserved verbatim', function () {
    $before = Yaml::parse(file_get_contents($this->vt100Path));
    $result = $this->reformatter->reformatTemplateFile($this->vt100Path);

    $after = Yaml::parse($result);

    expect($after['failure_criteria'])->toBe($before['failure_criteria']);
    expect($after['failure_criteria']['exit_codes'])->toBe([1, 2, 255]);
    expect($after['failure_criteria']['error_patterns'])->toBe(['Connection refused', 'Authentication failed']);
});

test('dead template keys are documented as deprecated', function () {
    $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

    foreach (['linebreak', 'hpAnyKeyPrmpt', 'pagerPrompt', 'pagerPromptCmd'] as $key) {
        expect($result)->toMatch('/^\s{2}' . $key . ':.*# DEPRECATED: This value is ignored$/m', "{$key} must be documented as deprecated.");
    }

    $this->assertStringNotContainsString('Linebreak setting', $result);
    $this->assertStringNotContainsString('HP-style prompt string', $result);
});

test('deprecated keys keep their values through a reformat', function () {
    $after = Yaml::parse($this->reformatter->reformatTemplateFile($this->oldFormatPath));

    expect($after['config']['linebreak'])->toBe('n');
    expect($after['config']['pagerPrompt'])->toBe('--More--');
    expect($after['auth']['hpAnyKeyPrmpt'])->toBe('Press any key to continue');
});

test('terminal dimensions are documented as ansi only', function () {
    $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

    expect($result)->toMatch('/^\s{2}setTerminalDimensions:.*# .*ANSI.*$/m');
    $this->assertStringNotContainsString('Terminal dimensions for Ansi sessions', $result);
});

test('reformatting is stable across repeated runs', function () {
    $once = $this->reformatter->reformatTemplate(file_get_contents($this->vt100Path));
    $twice = $this->reformatter->reformatTemplate($once);

    expect(Yaml::parse($twice))->toBe(Yaml::parse($once));
});
