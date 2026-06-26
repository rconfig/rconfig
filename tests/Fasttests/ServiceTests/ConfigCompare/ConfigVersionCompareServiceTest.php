<?php

namespace Tests\Fasttests\ServiceTests\ConfigCompare;

use App\Models\Command;
use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\Setting;
use App\Services\ConfigHistory\ConfigHistoryManager;
use App\Services\Templates\CompareExclusionTemplateService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigVersionCompareServiceTest extends TestCase
{
    private string $command = 'show run versioning test';
    private string $workDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        // Ensure the comparison settings + default exclusion file are present.
        (new CompareExclusionTemplateService)->installDefaultTemplate();

        Command::firstOrCreate(['command' => $this->command]);

        $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('vtest_', true) . '/');
        File::makeDirectory($this->workDir, 0777, true, true);
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

    private function makeConfig(string $filePath, ?int $version, int $latest): Config
    {
        return Config::create([
            'device_id' => 987654,
            'device_name' => 'versiontest-router',
            'device_category' => 'Routers',
            'command' => $this->command,
            'type' => 'device_download',
            'download_status' => 1,
            'config_location' => $filePath,
            'config_filename' => basename($filePath),
            'config_filesize' => filesize($filePath),
            'config_version' => $version,
            'latest_version' => $latest,
        ]);
    }

    public function test_first_config_version_is_set_to_one_with_no_change_record(): void
    {
        $current = $this->makeConfig($this->writeFile('v1.txt', "hostname r1\ninterface g0/0\n"), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        $this->assertSame(1, $current->fresh()->config_version);
        $this->assertSame(0, ConfigChange::where('current_config_id', $current->id)->count());
    }

    public function test_identical_config_reuses_previous_version_and_creates_no_change(): void
    {
        $content = "hostname r1\ninterface g0/0\n description uplink\n";
        $previous = $this->makeConfig($this->writeFile('prev.txt', $content), 1, 0);
        $current = $this->makeConfig($this->writeFile('curr.txt', $content), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        $this->assertSame(1, $current->fresh()->config_version);
        $this->assertSame(0, ConfigChange::where('current_config_id', $current->id)->count());
    }

    public function test_changed_config_bumps_version_and_records_a_change(): void
    {
        $previous = $this->makeConfig($this->writeFile('prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);
        $current = $this->makeConfig($this->writeFile('curr.txt', "hostname r1\ninterface g0/0\n ip address 10.0.0.1 255.255.255.0\n"), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        $this->assertSame(2, $current->fresh()->config_version);

        $change = ConfigChange::where('current_config_id', $current->id)->first();
        $this->assertNotNull($change);
        $this->assertSame($previous->id, $change->previous_config_id);
        $this->assertSame(2, $change->config_version);
        $this->assertSame('added', $change->config_change_type);
        $this->assertNotEmpty($change->config_diff);
    }

    public function test_zero_byte_current_config_does_not_change_version_or_record(): void
    {
        $this->makeConfig($this->writeFile('prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);
        // Current file is empty (0 bytes) -> treated as invalid, no version assigned.
        $current = $this->makeConfig($this->writeFile('curr.txt', ''), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        $this->assertNull($current->fresh()->config_version);
        $this->assertSame(0, ConfigChange::where('current_config_id', $current->id)->count());
    }

    public function test_unknown_command_does_not_version_the_config(): void
    {
        $current = $this->makeConfig($this->writeFile('v1.txt', "hostname r1\n"), null, 1);

        // No Command record exists for this name, so versioning is skipped.
        $result = (new ConfigHistoryManager)->handleNewDownloadedConfig($current, 'command that does not exist');

        $this->assertFalse($result);
        $this->assertNull($current->fresh()->config_version);
        $this->assertSame(0, ConfigChange::where('current_config_id', $current->id)->count());
    }

    public function test_change_only_on_excluded_line_creates_no_change_record(): void
    {
        // The default exclusion template drops "Last configuration change" lines.
        $previous = $this->makeConfig($this->writeFile('prev.txt', "! Last configuration change at 10:00\nhostname r1\n"), 1, 0);
        $current = $this->makeConfig($this->writeFile('curr.txt', "! Last configuration change at 11:30\nhostname r1\n"), null, 1);

        // Sanity: the exclusion file is installed.
        $this->assertNotEmpty(Setting::find(1)->config_compare_exclusion_file);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        $this->assertSame(1, $current->fresh()->config_version);
        $this->assertSame(0, ConfigChange::where('current_config_id', $current->id)->count());
    }
}
