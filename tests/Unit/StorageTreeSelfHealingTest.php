<?php

use App\Console\Commands\rConfigCloneTemplates;
use App\CustomClasses\CreateTaskReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    // A throwaway application root, so the real storage tree is untouched
    // and the directories under test genuinely start absent.
    $this->appDirPath = sys_get_temp_dir() . '/rconfig-storage-tree-' . uniqid();
    Config::set('rConfig.app_dir_path', $this->appDirPath);
});

afterEach(function () {
    if (is_dir($this->appDirPath)) {
        File::deleteDirectory($this->appDirPath);
    }

});

test('clone target is created when the templates directory is absent', function () {
    expect(templates_path())->not->toBeDirectory();

    $dstDir = templates_path() . 'rConfig-templates';

    // Only the directory creation, not the command's git clone.
    (new rConfigCloneTemplates)->ensureCloneDirectoryExists($dstDir);

    expect(templates_path())->toBeDirectory();
    expect($dstDir)->toBeDirectory();
});

test('clone target creation is idempotent', function () {
    $dstDir = templates_path() . 'rConfig-templates';
    $command = new rConfigCloneTemplates;

    $command->ensureCloneDirectoryExists($dstDir);
    $command->ensureCloneDirectoryExists($dstDir);

    expect($dstDir)->toBeDirectory();
});

test('saving a task report creates the report directory when absent', function () {
    expect(report_path())->not->toBeDirectory();

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

    expect(report_path())->toBeDirectory();
    expect($reportData->report_path)->toBeFile();
});
