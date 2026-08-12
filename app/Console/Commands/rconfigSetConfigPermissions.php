<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class rconfigSetConfigPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:set-config-permissions {--dry-run : Report what would change without altering anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-apply secure permissions to downloaded device configs already on disk';

    /**
     * Tightening the permissions in FileOperations only affects newly written
     * configs. Installs upgrading from a release that wrote world readable
     * configs keep those permissions on every historic file, so this walks the
     * config directories and brings them in line with the configured modes.
     */
    public function handle(): int
    {
        $fileMode = (int) config('rConfig.config_file_mode');
        $dirMode = (int) config('rConfig.config_dir_mode');
        $isDryRun = (bool) $this->option('dry-run');

        // The temp dir holds cleaned copies of config content during compares,
        // so it needs the same treatment as the config data dir. It is only
        // created on demand, so it may legitimately be absent.
        $targets = array_filter([
            config_data_path(),
            rconfig_appdir_storage_path() . '/app/rconfig/tempdir/',
        ], 'is_dir');

        if ($targets === []) {
            $this->warn('No config directories found, nothing to do: ' . config_data_path());

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%sApplying file mode %o and directory mode %o',
            $isDryRun ? '[dry run] ' : '',
            $fileMode,
            $dirMode
        ));

        $counts = ['files' => 0, 'dirs' => 0, 'failed' => 0];

        foreach ($targets as $path) {
            $this->line("  Scanning {$path}");

            // Include the target directory itself, not just its contents.
            $this->applyMode(new SplFileInfo($path), $dirMode, $isDryRun, $counts);

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                if ($item->isLink()) {
                    continue;
                }

                $this->applyMode($item, $item->isDir() ? $dirMode : $fileMode, $isDryRun, $counts);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s%d file(s) and %d director(ies) %s.',
            $isDryRun ? '[dry run] ' : '',
            $counts['files'],
            $counts['dirs'],
            $isDryRun ? 'would be changed' : 'updated'
        ));

        if ($counts['failed'] > 0) {
            $this->error(sprintf(
                '%d path(s) could not be changed. Re-run as the owner of the config data (usually the web server user) or as root.',
                $counts['failed']
            ));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Chmod a single path, skipping anything already at the target mode so the
     * summary reflects real changes rather than every path walked.
     *
     * @param  array{files: int, dirs: int, failed: int}  $counts
     */
    private function applyMode(SplFileInfo $item, int $mode, bool $isDryRun, array &$counts): void
    {
        $path = $item->getPathname();
        $current = fileperms($path);

        if ($current === false || ($current & 0777) === $mode) {
            return;
        }

        $key = $item->isDir() ? 'dirs' : 'files';

        if ($this->output->isVerbose()) {
            $this->line(sprintf('  %o -> %o  %s', $current & 0777, $mode, $path));
        }

        if ($isDryRun) {
            $counts[$key]++;

            return;
        }

        if (@chmod($path, $mode)) {
            $counts[$key]++;

            return;
        }

        $counts['failed']++;
        $this->warn("  Could not chmod: {$path}");
    }
}
