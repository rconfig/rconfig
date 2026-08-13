<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * A bind mounted storage volume masks everything the image ships under
 * storage/, so the container entrypoint has to restore the tree itself. The
 * only test that genuinely exercises that needs Docker and lives in the release
 * workflow. These are the cheap tripwires: they read the two files as text and
 * assert the pieces that, if dropped, silently reintroduce issue #357.
 */
class DockerEntrypointTest extends TestCase
{
    private const SKELETON_PATH = '/usr/local/share/rconfig/storage-skeleton';

    private function entrypoint(): string
    {
        return (string) file_get_contents(base_path('docker/entrypoint.sh'));
    }

    /**
     * The entrypoint with comment lines stripped, for assertions that care what
     * the script runs rather than what it says about itself. Comments here
     * discuss the approaches that were rejected, so a naive substring match
     * against the whole file finds them.
     */
    private function entrypointCommands(): string
    {
        $lines = preg_split('/\R/', $this->entrypoint()) ?: [];

        return implode("\n", array_filter($lines, static fn (string $line): bool => ! str_starts_with(ltrim($line), '#')));
    }

    private function dockerfile(): string
    {
        return (string) file_get_contents(base_path('Dockerfile'));
    }

    public function test_dockerfile_snapshots_the_storage_skeleton_outside_the_mount_point()
    {
        $dockerfile = $this->dockerfile();

        $this->assertStringContainsString(self::SKELETON_PATH, $dockerfile, 'The Dockerfile no longer builds the storage skeleton, so a bind mounted storage volume will start empty.');
        // cp -a is correct here, unlike in the entrypoint: this creates a fresh
        // copy at build time, so there is nothing existing to have its mode
        // rewritten, and -a is what carries the 0750 config data directory over.
        $this->assertStringContainsString('cp -a /var/www/html/rconfig/storage ' . self::SKELETON_PATH, $dockerfile, 'The skeleton must be copied with cp -a so modes are preserved rather than left to the build umask.');
        $this->assertStringNotContainsString('cp -r /var/www/html/rconfig/storage', $dockerfile, 'cp -r does not preserve modes. The skeleton carries the 0750 config data directory and must be copied with cp -a.');
    }

    /**
     * rconfig:clone-templates shells out to git. It was previously relying on
     * git arriving transitively from the base image.
     */
    public function test_dockerfile_installs_git_explicitly()
    {
        $this->assertMatchesRegularExpression('/^\s+git \\\\$/m', $this->dockerfile(), 'git is not in the apt install list, so rconfig:clone-templates depends on the base image happening to ship it.');
    }

    public function test_entrypoint_seeds_storage_from_the_skeleton_without_clobbering()
    {
        $entrypoint = $this->entrypoint();

        $this->assertStringContainsString(self::SKELETON_PATH, $entrypoint, 'The entrypoint no longer restores from the storage skeleton.');
        $this->assertStringContainsString('--skip-old-files', $entrypoint, 'The seed must skip existing files so device configs, logs and keys are never overwritten.');
    }

    /**
     * cp -a looks equivalent here and is not. It re-applies the skeleton's mode
     * to directories that already exist, so an operator who had tightened
     * storage/app/rconfig/data would find it widened again on every restart.
     * That is the failure mode 8.2.14 was about, so pin the choice.
     */
    public function test_entrypoint_does_not_seed_with_cp()
    {
        $commands = $this->entrypointCommands();

        $this->assertStringNotContainsString('cp -an', $commands, 'cp -a re-applies modes to existing directories. Seed with tar --skip-old-files instead.');
        $this->assertStringNotContainsString('cp -rn', $commands, 'cp -rn applies the umask to created directories, which would re-expose the config data directory.');
    }

    /**
     * set -e does not catch a failure on the left of a pipe, so a broken read
     * from the skeleton would otherwise look like a clean seed.
     */
    public function test_entrypoint_seed_pipeline_sets_pipefail()
    {
        $this->assertStringContainsString('set -o pipefail', $this->entrypoint(), 'The seed pipeline must set pipefail, or a failure in the reading tar is masked by the writing tar succeeding.');
    }

    /**
     * The gap that caused #357 was storage/app/rconfig being absent entirely.
     */
    public function test_entrypoint_creates_the_rconfig_storage_directories()
    {
        $entrypoint = $this->entrypoint();

        $required = [
            'storage/app/rconfig/templates',
            'storage/app/rconfig/reports',
            'storage/app/rconfig/data',
            'storage/app/rconfig/backups',
            'storage/app/rconfig/exports',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'storage/app/public',
        ];

        foreach ($required as $directory) {
            $this->assertStringContainsString($directory, $entrypoint, "The entrypoint no longer creates {$directory}, which is how #357 happened.");
        }
    }

    /**
     * The config data directory holds downloaded device configs. A plain mkdir
     * would leave it at the umask default and undo the 8.2.14 permission fix.
     */
    public function test_entrypoint_creates_the_config_data_directory_with_an_explicit_mode()
    {
        $this->assertStringContainsString('mkdir -p -m "${RCONFIG_CONFIG_DIR_MODE:-0750}"', $this->entrypoint(), 'The config data directory must be created with an explicit mode, not left to the umask.');
    }

    /**
     * A failed seed leaves exactly the broken state this fix exists to prevent,
     * except now with a reassuring log line above it.
     */
    public function test_entrypoint_fails_loudly_when_storage_cannot_be_seeded()
    {
        $entrypoint = $this->entrypoint();

        $this->assertStringNotContainsString('--skip-old-files 2>/dev/null', $entrypoint, 'The seed must not have its errors suppressed. A silent failure leaves exactly the state this fix prevents.');
        $this->assertStringContainsString('is not writable inside the container', $entrypoint, 'The entrypoint must detect an unwritable storage mount and say so.');
        $this->assertStringContainsString('add :z to the volume line', $entrypoint, 'SELinux hosts are a known cause of an unwritable bind mount, so the error should name the fix.');
    }
}
