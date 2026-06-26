<?php

namespace Tests\Fasttests\ServiceTests\ConfigCompare;

use App\Models\Setting;
use App\Services\ConfigCompare\ConfigCompareExclusionCleaner;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigCompareExclusionCleanerTest extends TestCase
{
    private string $workDir;
    private string $sampleFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('excl_', true) . '/');
        File::makeDirectory($this->workDir, 0777, true, true);

        $this->sampleFile = $this->writeFile('sample.txt', "hostname r1\n");

        Setting::where('id', 1)->update(['config_compare_exclusion_file' => $this->exclusionTemplate()]);
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();
        File::deleteDirectory($this->workDir);
        File::delete(File::glob(tmp_dir() . '/*.txt'));
        parent::tearDown();
    }

    private function writeFile(string $name, string $content): string
    {
        $path = $this->workDir . $name;
        File::put($path, $content);

        return $path;
    }

    private function exclusionTemplate(): string
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

    public function test_parser_returns_empty_when_no_exclusion_file_set(): void
    {
        Setting::where('id', 1)->update(['config_compare_exclusion_file' => null]);

        // Use a private workDir file so the auto-install of the default template does not run on a shared file.
        $cleaner = new ConfigCompareExclusionCleaner($this->sampleFile);

        $this->assertEmpty($cleaner->parseExclusionFile());
    }

    public function test_parses_exclusion_file_into_command_blocks_with_rules(): void
    {
        $parsed = (new ConfigCompareExclusionCleaner($this->sampleFile))->parseExclusionFile();

        $this->assertIsArray($parsed);
        $this->assertCount(3, $parsed);
        $this->assertSame('global', $parsed[0]['command']);
        $this->assertSame('show run', $parsed[1]['command']);
        $this->assertSame('show version', $parsed[2]['command']);
        $this->assertSame('/^Current configuration.*$/m', $parsed[0]['rules'][0]);
        $this->assertSame('/^.*nvram.*$/m', $parsed[1]['rules'][0]);
    }

    public function test_merges_global_and_command_specific_rules(): void
    {
        $cleaner = new ConfigCompareExclusionCleaner($this->sampleFile, 'show run');
        $parsed = $cleaner->parseExclusionFile();

        $merged = $cleaner->mergeGlobalAndCommandRules($parsed, 'show run');

        // Two global rules + one show run rule.
        $this->assertContains('/^Current configuration.*$/m', $merged);
        $this->assertContains('/^.*Last configuration change.*$/m', $merged);
        $this->assertContains('/^.*nvram.*$/m', $merged);
        // The show version rule must NOT be present.
        $this->assertNotContains('/^.*uptime is.*$/m', $merged);
    }

    public function test_excludes_global_and_command_specific_lines(): void
    {
        $content = "Current configuration : 1234 bytes\nhostname r1\nSome nvram content here\nSystem uptime is 5 days\n";
        $file = $this->writeFile('cfg.txt', $content);

        $cleaned = (new ConfigCompareExclusionCleaner($file, 'show run'))->excludeLines();
        $out = file_get_contents($cleaned);

        $this->assertStringNotContainsString('Current configuration', $out);
        $this->assertStringNotContainsString('Some nvram content here', $out);
        $this->assertStringContainsString('hostname r1', $out);
        // "uptime is" belongs to show version, not show run, so it stays.
        $this->assertStringContainsString('System uptime is 5 days', $out);
    }

    public function test_command_not_in_policy_applies_only_global_rules(): void
    {
        $content = "Current configuration : 1234 bytes\nSome nvram content here\nhostname r1\n";
        $file = $this->writeFile('cfg.txt', $content);

        $cleaned = (new ConfigCompareExclusionCleaner($file, 'show clock'))->excludeLines();
        $out = file_get_contents($cleaned);

        $this->assertStringNotContainsString('Current configuration', $out);
        // nvram is a show run rule; show clock is not in the policy, so only global rules apply.
        $this->assertStringContainsString('Some nvram content here', $out);
        $this->assertStringContainsString('hostname r1', $out);
    }

    public function test_invalid_regex_pattern_is_skipped_gracefully(): void
    {
        Setting::where('id', 1)->update(['config_compare_exclusion_file' => "// faulty\n#[global]\n/^Current configuration.*$/m\n/(unclosed/m\n"]);

        $content = "Current configuration : 1234 bytes\nhostname r1\n";
        $file = $this->writeFile('cfg.txt', $content);

        $cleaned = (new ConfigCompareExclusionCleaner($file))->excludeLines();
        $out = file_get_contents($cleaned);

        // The valid rule still applies; the faulty one is skipped without throwing.
        $this->assertStringNotContainsString('Current configuration', $out);
        $this->assertStringContainsString('hostname r1', $out);
    }

    public function test_multiline_pattern_removes_whole_block(): void
    {
        Setting::where('id', 1)->update(['config_compare_exclusion_file' => "// global\n#[global]\n/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s\n"]);

        $content = "hostname r1\n-----BEGIN CERTIFICATE-----\nAAAABBBBCCCC\nDDDDEEEE\n-----END CERTIFICATE-----\nlogging host 10.0.0.1\n";
        $file = $this->writeFile('cfg.txt', $content);

        $cleaned = (new ConfigCompareExclusionCleaner($file))->excludeLines();
        $out = file_get_contents($cleaned);

        $this->assertStringNotContainsString('BEGIN CERTIFICATE', $out);
        $this->assertStringNotContainsString('AAAABBBBCCCC', $out);
        $this->assertStringContainsString('hostname r1', $out);
        $this->assertStringContainsString('logging host 10.0.0.1', $out);
    }

    public function test_ignore_whitespace_setting_normalizes_empty_lines(): void
    {
        // Non-empty exclusion file (no rules) so the cleaner does not auto-install
        // the default template, which would reset config_compare_settings.
        Setting::where('id', 1)->update([
            'config_compare_exclusion_file' => "// global\n#[global]\n",
            'config_compare_settings' => [
                'context' => 3, 'ignoreCase' => false, 'ignoreLineEnding' => false, 'ignoreWhitespace' => true, 'lengthLimit' => 20000,
            ],
        ]);

        $a = $this->writeFile('a.txt', "hostname r1\ninterface g0/0\nend");
        $b = $this->writeFile('b.txt', "hostname r1\n\ninterface g0/0\n   \nend");

        $cleanedA = file_get_contents((new ConfigCompareExclusionCleaner($a, 'show run'))->excludeLines());
        $cleanedB = file_get_contents((new ConfigCompareExclusionCleaner($b, 'show run'))->excludeLines());

        $this->assertSame($cleanedA, $cleanedB);
    }

    public function test_clean_temp_files_removes_generated_temp_files(): void
    {
        $content = "Current configuration : 1234 bytes\nhostname r1\n";
        $file = $this->writeFile('cfg.txt', $content);

        $cleaner = new ConfigCompareExclusionCleaner($file, 'show run');
        $cleaned = $cleaner->excludeLines();

        // A temp file was created because content changed.
        $this->assertNotSame($file, $cleaned);
        $this->assertNotEmpty(File::glob(tmp_dir() . '/*.txt'));

        $cleaner->cleanTempFiles();
        $this->assertEmpty(File::glob(tmp_dir() . '/*.txt'));
    }

    public function test_installs_default_template_when_exclusion_file_is_null(): void
    {
        Setting::where('id', 1)->update(['config_compare_exclusion_file' => null, 'config_compare_settings' => null]);

        $file = $this->writeFile('cfg.txt', "hostname r1\n");
        (new ConfigCompareExclusionCleaner($file))->excludeLines();

        // The cleaner auto-installs the default exclusion template when none exists.
        $this->assertNotEmpty(Setting::find(1)->config_compare_exclusion_file);
    }
}
