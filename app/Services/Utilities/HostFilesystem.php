<?php

namespace App\Services\Utilities;

/**
 * Thin wrapper over the OS calls used to create directories and hand them to
 * the web user. The global helpers in app/Http/Helpers.php resolve this from
 * the container, so tests can swap in a fake rather than needing to run as
 * root. Keep it free of logic: one method per OS call.
 */
class HostFilesystem
{
    /**
     * Contents of /etc/os-release, or null when it is absent or unreadable.
     */
    public function readOsRelease(): ?string
    {
        if (! is_readable('/etc/os-release')) {
            return null;
        }

        $contents = @file_get_contents('/etc/os-release');

        return $contents === false ? null : $contents;
    }

    /**
     * Whether the current process has an effective uid of 0.
     */
    public function isRoot(): bool
    {
        return function_exists('posix_geteuid') && posix_geteuid() === 0;
    }

    /**
     * Whether this process runs inside a Docker container.
     */
    public function isDocker(): bool
    {
        return isDocker();
    }

    public function isDirectory(string $path): bool
    {
        clearstatcache(true, $path);

        return is_dir($path);
    }

    /**
     * Create a single directory level (not recursive). Returns false rather
     * than raising, so the caller can tell a lost race from a real failure.
     */
    public function makeDirectory(string $path, int $mode): bool
    {
        return @mkdir($path, $mode);
    }

    public function chmod(string $path, int $mode): bool
    {
        return @chmod($path, $mode);
    }

    public function chown(string $path, string $user): bool
    {
        return @chown($path, $user);
    }

    public function chgrp(string $path, string $group): bool
    {
        return @chgrp($path, $group);
    }

    public function fileExists(string $path): bool
    {
        clearstatcache(true, $path);

        return file_exists($path);
    }

    /**
     * Every process visible in /proc, with its effective uid and arguments.
     * Processes that exit while being read are skipped.
     *
     * @return list<array{pid: int, uid: int, arguments: list<string>}>
     */
    public function processes(): array
    {
        $processes = [];

        foreach (glob('/proc/[0-9]*', GLOB_ONLYDIR) ?: [] as $dir) {
            $status = @file_get_contents("{$dir}/status");
            $cmdline = @file_get_contents("{$dir}/cmdline");

            if ($status === false || $cmdline === false || ! preg_match('/^Uid:\s+\d+\s+(\d+)/m', $status, $uid)) {
                continue;
            }

            $processes[] = [
                'pid' => (int) basename($dir),
                'uid' => (int) $uid[1],
                'arguments' => array_values(array_filter(explode("\0", $cmdline), fn (string $argument): bool => $argument !== '')),
            ];
        }

        return $processes;
    }

    /**
     * The user name for a uid, or null when it has none.
     */
    public function userName(int $uid): ?string
    {
        if (! function_exists('posix_getpwuid')) {
            return null;
        }

        $entry = posix_getpwuid($uid);

        return $entry === false ? null : $entry['name'];
    }
}
