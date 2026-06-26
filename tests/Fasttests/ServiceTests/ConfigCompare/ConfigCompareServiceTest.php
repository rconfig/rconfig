<?php

namespace Tests\Fasttests\ServiceTests\ConfigCompare;

use App\Models\Setting;
use App\Services\ConfigCompare\ConfigCompareService;
use App\Services\Templates\CompareExclusionTemplateService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigCompareServiceTest extends TestCase
{
    private string $workDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        (new CompareExclusionTemplateService)->installDefaultTemplate();

        $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('cmpsvc_', true) . '/');
        File::makeDirectory($this->workDir, 0777, true, true);
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();
        File::deleteDirectory($this->workDir);
        parent::tearDown();
    }

    private function writeFile(string $name, string $content): string
    {
        $path = $this->workDir . $name;
        File::put($path, $content);

        return $path;
    }

    public function test_diff_type_returns_none_for_identical_files(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\ninterface g0/0\n");
        $b = $this->writeFile('b.txt', "hostname r1\ninterface g0/0\n");

        $result = (new ConfigCompareService($a, $b))->file_content_compare();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('diff', $result);
        $this->assertArrayHasKey('diff_type', $result);
        $this->assertSame('none', $result['diff_type']);
    }

    public function test_diff_type_returns_changed_for_replaced_content(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\n ip address 10.0.0.1\n");
        $b = $this->writeFile('b.txt', "hostname r1\n ip address 10.0.0.2\n");

        $result = (new ConfigCompareService($a, $b))->file_content_compare();

        $this->assertSame('changed', $result['diff_type']);
        $this->assertStringContainsString('<table', $result['diff']);
    }

    public function test_diff_type_returns_added_for_new_lines(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\n");
        $b = $this->writeFile('b.txt', "hostname r1\ninterface g0/0\n");

        $result = (new ConfigCompareService($a, $b))->file_content_compare();

        $this->assertSame('added', $result['diff_type']);
        $this->assertStringContainsString('change-ins', $result['diff']);
        $this->assertStringNotContainsString('<del>', $result['diff']);
    }

    public function test_diff_type_returns_deleted_for_removed_lines(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\ninterface g0/0\n");
        $b = $this->writeFile('b.txt', "hostname r1\n");

        $result = (new ConfigCompareService($a, $b))->file_content_compare();

        $this->assertSame('deleted', $result['diff_type']);
        $this->assertStringContainsString('change-del', $result['diff']);
        $this->assertStringNotContainsString('<ins>', $result['diff']);
    }

    public function test_returns_error_when_a_file_is_missing(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\n");
        $missing = $this->workDir . 'does-not-exist.txt';

        $result = (new ConfigCompareService($a, $missing))->file_content_compare();

        $this->assertArrayHasKey('error', $result);
    }

    public function test_check_hash_match_detects_identical_and_different_files(): void
    {
        $a = $this->writeFile('a.txt', "hostname r1\n");
        $same = $this->writeFile('same.txt', "hostname r1\n");
        $different = $this->writeFile('diff.txt', "hostname r2\n");

        $this->assertTrue((new ConfigCompareService($a, $same))->check_hash_match());
        $this->assertFalse((new ConfigCompareService($a, $different))->check_hash_match());
    }

    public function test_stored_compare_settings_are_applied_by_the_service(): void
    {
        // Files differ only by case.
        $a = $this->writeFile('a.txt', "hostname ROUTER1\n");
        $b = $this->writeFile('b.txt', "hostname router1\n");

        // With ignoreCase off, the case difference is a real change.
        Setting::where('id', 1)->update(['config_compare_settings' => [
            'context' => 3, 'ignoreCase' => false, 'ignoreLineEnding' => false, 'ignoreWhitespace' => false, 'lengthLimit' => 20000,
        ]]);
        $this->assertSame('changed', (new ConfigCompareService($a, $b))->file_content_compare()['diff_type']);

        // With ignoreCase on, the same files are considered identical.
        Setting::where('id', 1)->update(['config_compare_settings' => [
            'context' => 3, 'ignoreCase' => true, 'ignoreLineEnding' => false, 'ignoreWhitespace' => false, 'lengthLimit' => 20000,
        ]]);
        $this->assertSame('none', (new ConfigCompareService($a, $b))->file_content_compare()['diff_type']);
    }

    public function test_falls_back_to_default_options_when_settings_are_null(): void
    {
        Setting::where('id', 1)->update(['config_compare_settings' => null]);

        $a = $this->writeFile('a.txt', "hostname r1\n");
        $b = $this->writeFile('b.txt', "hostname r1\n");

        $result = (new ConfigCompareService($a, $b))->file_content_compare();

        $this->assertSame('none', $result['diff_type']);
    }
}
