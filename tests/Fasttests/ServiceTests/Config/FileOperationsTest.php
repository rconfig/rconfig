<?php

namespace Tests\Fasttests\ServiceTests\Config;

use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Downloaded device configs contain secrets (VPN pre shared keys, RADIUS
 * secrets, SNMP community strings). These tests pin the on disk modes so the
 * world readable regression reported in issue #354 cannot return silently.
 */
class FileOperationsTest extends TestCase
{
    private string $dataBaseDir;
    private string $categoryName = 'TestCat';
    private string $deviceName = 'TestDevice';

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->dataBaseDir = storage_path('app/rconfig/tempconfigs/' . uniqid('fileops_', true) . '/');
        File::makeDirectory($this->dataBaseDir, 0750, true, true);
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();

        if (isset($this->dataBaseDir) && File::exists($this->dataBaseDir)) {
            File::deleteDirectory($this->dataBaseDir);
        }

        parent::tearDown();
    }

    public function test_saved_config_file_is_not_readable_by_other(): void
    {
        $result = $this->saveConfig(['hostname r1', 'snmp-server community s3cr3t RO']);

        $this->assertTrue($result['download_status']);
        $this->assertFileExists($result['filepath']);

        $this->assertSame(
            0440,
            $this->modeOf($result['filepath']),
            'Stored config files must be 0440. A world readable config exposes device secrets to any local account.'
        );

        $this->assertSame(
            0,
            fileperms($result['filepath']) & 0007,
            'Stored config files must have no "other" permission bits.'
        );
    }

    public function test_created_directories_are_not_traversable_by_other(): void
    {
        $result = $this->saveConfig(['hostname r1']);

        // A tight file mode is defeated by a traversable parent, so every level
        // of the category/device/year/month/day tree has to be checked.
        $directory = dirname($result['filepath']);

        while (rtrim($directory, '/') !== rtrim($this->dataBaseDir, '/')) {
            $this->assertSame(
                0750,
                $this->modeOf($directory),
                "Config directory must be 0750, got a looser mode on: {$directory}"
            );

            $this->assertSame(
                0,
                fileperms($directory) & 0007,
                "Config directory must not be traversable or readable by other: {$directory}"
            );

            $directory = dirname($directory);
        }
    }

    public function test_file_contents_are_written_correctly(): void
    {
        $result = $this->saveConfig(['line one', 'line two']);

        $this->assertSame('line one' . PHP_EOL . 'line two', file_get_contents($result['filepath']));
        $this->assertSame(strlen('line one' . PHP_EOL . 'line two'), $result['filesize']);
    }

    public function test_existing_read_only_config_can_be_overwritten_by_a_later_download(): void
    {
        $first = $this->saveConfig(['first download']);
        $this->assertSame(0440, $this->modeOf($first['filepath']));

        // The second download reuses the same path (same command, same minute).
        // It must reopen the read only file, rewrite it, and re-lock it.
        $second = $this->saveConfig(['second download']);

        $this->assertSame($first['filepath'], $second['filepath'], 'Expected the same target path for this assertion to be meaningful.');
        $this->assertTrue($second['download_status']);
        $this->assertSame('second download', file_get_contents($second['filepath']));
        $this->assertSame(0440, $this->modeOf($second['filepath']), 'The file must be returned to read only after being rewritten.');
    }

    /**
     * The write window between creating the file and inserting its contents was
     * previously 0666, so the file was briefly world writable as well as world
     * readable. Asserting only the final mode would not catch a regression here.
     */
    public function test_write_window_grants_no_access_to_other(): void
    {
        $fileops = new FileOperations(
            'show running-config',
            $this->categoryName,
            $this->deviceName,
            1,
            $this->dataBaseDir,
            'cli'
        );

        $path = $fileops->createFile('show running-config');

        $this->assertFileExists($path);
        $this->assertSame(0660, $this->modeOf($path), 'The write window must be 0660, never 0666.');
        $this->assertSame(0, fileperms($path) & 0007, 'The write window must not expose the file to other.');
    }

    public function test_write_window_follows_a_tightened_file_mode(): void
    {
        config(['rConfig.config_file_mode' => 0400]);

        $fileops = new FileOperations(
            'show running-config',
            $this->categoryName,
            $this->deviceName,
            1,
            $this->dataBaseDir,
            'cli'
        );

        // An operator tightening the file mode to owner only must not have the
        // write window silently widen it back to the group.
        $this->assertSame(0600, $this->modeOf($fileops->createFile('show running-config')));
    }

    public function test_configured_modes_are_honoured(): void
    {
        config(['rConfig.config_file_mode' => 0400, 'rConfig.config_dir_mode' => 0700]);

        $result = $this->saveConfig(['hostname r1']);

        $this->assertSame(0400, $this->modeOf($result['filepath']));
        $this->assertSame(0700, $this->modeOf(dirname($result['filepath'])));
    }

    public function test_directory_mode_is_not_weakened_by_the_process_umask(): void
    {
        // mkdir()'s mode argument is masked by the umask, so a permissive umask
        // used to be enough to leave the tree looser than intended.
        $originalUmask = umask(0);

        try {
            $result = $this->saveConfig(['hostname r1']);

            $this->assertSame(0750, $this->modeOf(dirname($result['filepath'])));
            $this->assertSame(0440, $this->modeOf($result['filepath']));
        } finally {
            umask($originalUmask);
        }
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{filepath: string, filename: string, download_status: bool, filesize: int}
     */
    private function saveConfig(array $lines): array
    {
        $fileops = new FileOperations(
            'show running-config',
            $this->categoryName,
            $this->deviceName,
            1,
            $this->dataBaseDir,
            'cli'
        );

        return $fileops->saveFile($lines);
    }

    private function modeOf(string $path): int
    {
        clearstatcache(true, $path);

        return fileperms($path) & 0777;
    }
}
