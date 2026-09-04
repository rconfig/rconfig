<?php

use App\Models\Setting;
use App\Services\ConfigCompare\ConfigCompareService;
use App\Services\Templates\CompareExclusionTemplateService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    (new CompareExclusionTemplateService)->installDefaultTemplate();

    $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('cmpsvc_', true) . '/');
    File::makeDirectory($this->workDir, 0777, true, true);
});

afterEach(function () {
    $this->rollBackTransaction();
    File::deleteDirectory($this->workDir);
});

function compareServiceWriteFile(string $workDir, string $name, string $content): string
{
    $path = $workDir . $name;
    File::put($path, $content);

    return $path;
}

test('diff type returns none for identical files', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\ninterface g0/0\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname r1\ninterface g0/0\n");

    $result = (new ConfigCompareService($a, $b))->file_content_compare();

    expect($result)->toBeArray();
    expect($result)->toHaveKey('diff');
    expect($result)->toHaveKey('diff_type');
    expect($result['diff_type'])->toBe('none');
});

test('diff type returns changed for replaced content', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\n ip address 10.0.0.1\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname r1\n ip address 10.0.0.2\n");

    $result = (new ConfigCompareService($a, $b))->file_content_compare();

    expect($result['diff_type'])->toBe('changed');
    $this->assertStringContainsString('<table', $result['diff']);
});

test('diff type returns added for new lines', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname r1\ninterface g0/0\n");

    $result = (new ConfigCompareService($a, $b))->file_content_compare();

    expect($result['diff_type'])->toBe('added');
    $this->assertStringContainsString('change-ins', $result['diff']);
    $this->assertStringNotContainsString('<del>', $result['diff']);
});

test('diff type returns deleted for removed lines', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\ninterface g0/0\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname r1\n");

    $result = (new ConfigCompareService($a, $b))->file_content_compare();

    expect($result['diff_type'])->toBe('deleted');
    $this->assertStringContainsString('change-del', $result['diff']);
    $this->assertStringNotContainsString('<ins>', $result['diff']);
});

test('returns error when a file is missing', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\n");
    $missing = $this->workDir . 'does-not-exist.txt';

    $result = (new ConfigCompareService($a, $missing))->file_content_compare();

    expect($result)->toHaveKey('error');
});

test('check hash match detects identical and different files', function () {
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\n");
    $same = compareServiceWriteFile($this->workDir, 'same.txt', "hostname r1\n");
    $different = compareServiceWriteFile($this->workDir, 'diff.txt', "hostname r2\n");

    expect((new ConfigCompareService($a, $same))->check_hash_match())->toBeTrue();
    expect((new ConfigCompareService($a, $different))->check_hash_match())->toBeFalse();
});

test('stored compare settings are applied by the service', function () {
    // Files differ only by case.
    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname ROUTER1\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname router1\n");

    // With ignoreCase off, the case difference is a real change.
    Setting::where('id', 1)->update(['config_compare_settings' => [
        'context' => 3, 'ignoreCase' => false, 'ignoreLineEnding' => false, 'ignoreWhitespace' => false, 'lengthLimit' => 20000,
    ]]);
    expect((new ConfigCompareService($a, $b))->file_content_compare()['diff_type'])->toBe('changed');

    // With ignoreCase on, the same files are considered identical.
    Setting::where('id', 1)->update(['config_compare_settings' => [
        'context' => 3, 'ignoreCase' => true, 'ignoreLineEnding' => false, 'ignoreWhitespace' => false, 'lengthLimit' => 20000,
    ]]);
    expect((new ConfigCompareService($a, $b))->file_content_compare()['diff_type'])->toBe('none');
});

test('falls back to default options when settings are null', function () {
    Setting::where('id', 1)->update(['config_compare_settings' => null]);

    $a = compareServiceWriteFile($this->workDir, 'a.txt', "hostname r1\n");
    $b = compareServiceWriteFile($this->workDir, 'b.txt', "hostname r1\n");

    $result = (new ConfigCompareService($a, $b))->file_content_compare();

    expect($result['diff_type'])->toBe('none');
});
