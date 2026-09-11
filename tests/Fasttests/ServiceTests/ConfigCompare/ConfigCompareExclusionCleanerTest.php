<?php

use App\Models\Setting;
use App\Services\ConfigCompare\ConfigCompareExclusionCleaner;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('excl_', true) . '/');
    File::makeDirectory($this->workDir, 0777, true, true);

    $this->sampleFile = exclusionCleanerWriteFile($this->workDir, 'sample.txt', "hostname r1\n");

    Setting::where('id', 1)->update(['config_compare_exclusion_file' => exclusionTemplate()]);
});

afterEach(function () {
    $this->rollBackTransaction();
    File::deleteDirectory($this->workDir);
    File::delete(File::glob(tmp_dir() . '/*.txt'));
});

function exclusionCleanerWriteFile(string $workDir, string $name, string $content): string
{
    $path = $workDir . $name;
    File::put($path, $content);

    return $path;
}

function exclusionTemplate(): string
{
    return '// Description: This is the global exclusion Policy list
#[global]
/^Current configuration.*$/m
/^.*Last configuration change.*$/m

// Description: This is the Show Run specific Exclusion List
#[show run]
/^.*nvram.*$/m

// Description: This is the Show Version specific Exclusion List
#[show version]
/^.*uptime is.*$/m';
}

test('parser returns empty when no exclusion file set', function () {
    Setting::where('id', 1)->update(['config_compare_exclusion_file' => null]);

    // Use a private workDir file so the auto-install of the default template does not run on a shared file.
    $cleaner = new ConfigCompareExclusionCleaner($this->sampleFile);

    expect($cleaner->parseExclusionFile())->toBeEmpty();
});

test('parses exclusion file into command blocks with rules', function () {
    $parsed = (new ConfigCompareExclusionCleaner($this->sampleFile))->parseExclusionFile();

    expect($parsed)->toBeArray();
    expect($parsed)->toHaveCount(3);
    expect($parsed[0]['command'])->toBe('global');
    expect($parsed[1]['command'])->toBe('show run');
    expect($parsed[2]['command'])->toBe('show version');
    expect($parsed[0]['rules'][0])->toBe('/^Current configuration.*$/m');
    expect($parsed[1]['rules'][0])->toBe('/^.*nvram.*$/m');
});

test('merges global and command specific rules', function () {
    $cleaner = new ConfigCompareExclusionCleaner($this->sampleFile, 'show run');
    $parsed = $cleaner->parseExclusionFile();

    $merged = $cleaner->mergeGlobalAndCommandRules($parsed, 'show run');

    // Two global rules + one show run rule.
    expect($merged)->toContain('/^Current configuration.*$/m');
    expect($merged)->toContain('/^.*Last configuration change.*$/m');
    expect($merged)->toContain('/^.*nvram.*$/m');

    // The show version rule must NOT be present.
    expect($merged)->not->toContain('/^.*uptime is.*$/m');
});

test('excludes global and command specific lines', function () {
    $content = "Current configuration : 1234 bytes\nhostname r1\nSome nvram content here\nSystem uptime is 5 days\n";
    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', $content);

    $cleaned = (new ConfigCompareExclusionCleaner($file, 'show run'))->excludeLines();
    $out = file_get_contents($cleaned);

    $this->assertStringNotContainsString('Current configuration', $out);
    $this->assertStringNotContainsString('Some nvram content here', $out);
    $this->assertStringContainsString('hostname r1', $out);

    // "uptime is" belongs to show version, not show run, so it stays.
    $this->assertStringContainsString('System uptime is 5 days', $out);
});

test('command not in policy applies only global rules', function () {
    $content = "Current configuration : 1234 bytes\nSome nvram content here\nhostname r1\n";
    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', $content);

    $cleaned = (new ConfigCompareExclusionCleaner($file, 'show clock'))->excludeLines();
    $out = file_get_contents($cleaned);

    $this->assertStringNotContainsString('Current configuration', $out);

    // nvram is a show run rule; show clock is not in the policy, so only global rules apply.
    $this->assertStringContainsString('Some nvram content here', $out);
    $this->assertStringContainsString('hostname r1', $out);
});

test('invalid regex pattern is skipped gracefully', function () {
    Setting::where('id', 1)->update(['config_compare_exclusion_file' => "// faulty\n#[global]\n/^Current configuration.*$/m\n/(unclosed/m\n"]);

    $content = "Current configuration : 1234 bytes\nhostname r1\n";
    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', $content);

    $cleaned = (new ConfigCompareExclusionCleaner($file))->excludeLines();
    $out = file_get_contents($cleaned);

    // The valid rule still applies; the faulty one is skipped without throwing.
    $this->assertStringNotContainsString('Current configuration', $out);
    $this->assertStringContainsString('hostname r1', $out);
});

test('multiline pattern removes whole block', function () {
    Setting::where('id', 1)->update(['config_compare_exclusion_file' => "// global\n#[global]\n/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s\n"]);

    $content = "hostname r1\n-----BEGIN CERTIFICATE-----\nAAAABBBBCCCC\nDDDDEEEE\n-----END CERTIFICATE-----\nlogging host 10.0.0.1\n";
    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', $content);

    $cleaned = (new ConfigCompareExclusionCleaner($file))->excludeLines();
    $out = file_get_contents($cleaned);

    $this->assertStringNotContainsString('BEGIN CERTIFICATE', $out);
    $this->assertStringNotContainsString('AAAABBBBCCCC', $out);
    $this->assertStringContainsString('hostname r1', $out);
    $this->assertStringContainsString('logging host 10.0.0.1', $out);
});

test('ignore whitespace setting normalizes empty lines', function () {
    // Non-empty exclusion file (no rules) so the cleaner does not auto-install
    // the default template, which would reset config_compare_settings.
    Setting::where('id', 1)->update([
        'config_compare_exclusion_file' => "// global\n#[global]\n",
        'config_compare_settings' => [
            'context' => 3, 'ignoreCase' => false, 'ignoreLineEnding' => false, 'ignoreWhitespace' => true, 'lengthLimit' => 20000,
        ],
    ]);

    $a = exclusionCleanerWriteFile($this->workDir, 'a.txt', "hostname r1\ninterface g0/0\nend");
    $b = exclusionCleanerWriteFile($this->workDir, 'b.txt', "hostname r1\n\ninterface g0/0\n   \nend");

    $cleanedA = file_get_contents((new ConfigCompareExclusionCleaner($a, 'show run'))->excludeLines());
    $cleanedB = file_get_contents((new ConfigCompareExclusionCleaner($b, 'show run'))->excludeLines());

    expect($cleanedB)->toBe($cleanedA);
});

test('clean temp files removes generated temp files', function () {
    $content = "Current configuration : 1234 bytes\nhostname r1\n";
    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', $content);

    $cleaner = new ConfigCompareExclusionCleaner($file, 'show run');
    $cleaned = $cleaner->excludeLines();

    // A temp file was created because content changed.
    $this->assertNotSame($file, $cleaned);
    expect(File::glob(tmp_dir() . '/*.txt'))->not->toBeEmpty();

    $cleaner->cleanTempFiles();
    expect(File::glob(tmp_dir() . '/*.txt'))->toBeEmpty();
});

test('installs default template when exclusion file is null', function () {
    Setting::where('id', 1)->update(['config_compare_exclusion_file' => null, 'config_compare_settings' => null]);

    $file = exclusionCleanerWriteFile($this->workDir, 'cfg.txt', "hostname r1\n");
    (new ConfigCompareExclusionCleaner($file))->excludeLines();

    // The cleaner auto-installs the default exclusion template when none exists.
    expect(Setting::find(1)->config_compare_exclusion_file)->not->toBeEmpty();
});
