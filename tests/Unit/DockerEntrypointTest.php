<?php

const SKELETON_PATH = '/usr/local/share/rconfig/storage-skeleton';

function entrypoint(): string
{
    return (string) file_get_contents(base_path('docker/entrypoint.sh'));
}

/**
 * The entrypoint with comment lines stripped, for assertions that care what
 * the script runs rather than what it says about itself. Comments here
 * discuss the approaches that were rejected, so a naive substring match
 * against the whole file finds them.
 */
function entrypointCommands(): string
{
    $lines = preg_split('/\R/', entrypoint()) ?: [];

    return implode("\n", array_filter($lines, static fn (string $line): bool => ! str_starts_with(ltrim($line), '#')));
}

function dockerfile(): string
{
    return (string) file_get_contents(base_path('Dockerfile'));
}

test('dockerfile snapshots the storage skeleton outside the mount point', function () {
    $dockerfile = dockerfile();

    $this->assertStringContainsString(SKELETON_PATH, $dockerfile, 'The Dockerfile no longer builds the storage skeleton, so a bind mounted storage volume will start empty.');

    // cp -a is correct here, unlike in the entrypoint: this creates a fresh
    // copy at build time, so there is nothing existing to have its mode
    // rewritten, and -a is what carries the 0750 config data directory over.
    $this->assertStringContainsString('cp -a /var/www/html/rconfig/storage ' . SKELETON_PATH, $dockerfile, 'The skeleton must be copied with cp -a so modes are preserved rather than left to the build umask.');
    $this->assertStringNotContainsString('cp -r /var/www/html/rconfig/storage', $dockerfile, 'cp -r does not preserve modes. The skeleton carries the 0750 config data directory and must be copied with cp -a.');
});

test('dockerfile installs git explicitly', function () {
    expect(dockerfile())->toMatch('/^\s+git \\\\$/m', 'git is not in the apt install list, so rconfig:clone-templates depends on the base image happening to ship it.');
});

test('entrypoint seeds storage from the skeleton without clobbering', function () {
    $entrypoint = entrypoint();

    $this->assertStringContainsString(SKELETON_PATH, $entrypoint, 'The entrypoint no longer restores from the storage skeleton.');
    $this->assertStringContainsString('--skip-old-files', $entrypoint, 'The seed must skip existing files so device configs, logs and keys are never overwritten.');
});

test('entrypoint does not seed with cp', function () {
    $commands = entrypointCommands();

    $this->assertStringNotContainsString('cp -an', $commands, 'cp -a re-applies modes to existing directories. Seed with tar --skip-old-files instead.');
    $this->assertStringNotContainsString('cp -rn', $commands, 'cp -rn applies the umask to created directories, which would re-expose the config data directory.');
});

test('entrypoint seed pipeline sets pipefail', function () {
    $this->assertStringContainsString('set -o pipefail', entrypoint(), 'The seed pipeline must set pipefail, or a failure in the reading tar is masked by the writing tar succeeding.');
});

test('entrypoint creates the rconfig storage directories', function () {
    $entrypoint = entrypoint();

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
});

test('entrypoint creates the config data directory with an explicit mode', function () {
    $this->assertStringContainsString('mkdir -p -m "${RCONFIG_CONFIG_DIR_MODE:-0750}"', entrypoint(), 'The config data directory must be created with an explicit mode, not left to the umask.');
});

test('entrypoint fails loudly when storage cannot be seeded', function () {
    $entrypoint = entrypoint();

    $this->assertStringNotContainsString('--skip-old-files 2>/dev/null', $entrypoint, 'The seed must not have its errors suppressed. A silent failure leaves exactly the state this fix prevents.');
    $this->assertStringContainsString('is not writable inside the container', $entrypoint, 'The entrypoint must detect an unwritable storage mount and say so.');
    $this->assertStringContainsString('add :z to the volume line', $entrypoint, 'SELinux hosts are a known cause of an unwritable bind mount, so the error should name the fix.');
});
