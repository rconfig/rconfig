<?php

namespace Tests\Unit;

use App\Console\Commands\rConfigCloneTemplates;
use App\CustomClasses\CreateTaskReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The Docker entrypoint seeds the storage tree, but a bare metal install can
 * reach the same broken state a different way: a restored backup, a migrated
 * data directory, a manually moved storage path. These cover the two writes
 * that used to throw rather than create the directory they needed. See #357.
 */
class StorageTreeSelfHealingTest extends TestCase
{
    private string $appDirPath;

    public function setUp(): void
    {
        parent::setUp();

        // A throwaway application root, so the real storage tree is untouched
        // and the directories under test genuinely start absent.
        $this->appDirPath = sys_get_temp_dir() . '/rconfig-storage-tree-' . uniqid();
        Config::set('rConfig.app_dir_path', $this->appDirPath);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->appDirPath)) {
            File::deleteDirectory($this->appDirPath);
        }

        parent::tearDown();
    }

    public function test_clone_target_is_created_when_the_templates_directory_is_absent()
    {
        $this->assertDirectoryDoesNotExist(templates_path());

        $dstDir = templates_path() . 'rConfig-templates';

        // Only the directory creation, not the command's git clone.
        (new rConfigCloneTemplates)->ensureCloneDirectoryExists($dstDir);

        $this->assertDirectoryExists(templates_path());
        $this->assertDirectoryExists($dstDir);
    }

    public function test_clone_target_creation_is_idempotent()
    {
        $dstDir = templates_path() . 'rConfig-templates';
        $command = new rConfigCloneTemplates;

        $command->ensureCloneDirectoryExists($dstDir);
        $command->ensureCloneDirectoryExists($dstDir);

        $this->assertDirectoryExists($dstDir);
    }

    public function test_saving_a_task_report_creates_the_report_directory_when_absent()
    {
        $this->assertDirectoryDoesNotExist(report_path());

        $reportId = (string) Str::uuid();

        $reportData = (object) [
            'report_id' => $reportId,
            'report_name' => 'Storage tree test report',
            'task_type' => 'manual',
            'task' => (object) [
                'id' => 1,
                'task_name' => 'Storage tree test task',
                'task_desc' => 'Covers the report directory guard',
                'verbose_download_report_notify' => 0,
            ],
            'start_time' => Carbon::now()->subMinute(),
            'end_time' => Carbon::now(),
            'file_name' => $reportId . '.html',
            'report_path' => report_path() . $reportId . '.html',
            'config_data_success' => collect(),
            'config_data_failed' => collect(),
        ];

        (new CreateTaskReport($reportData))->saveReport();

        $this->assertDirectoryExists(report_path());
        $this->assertFileExists($reportData->report_path);
    }
}
