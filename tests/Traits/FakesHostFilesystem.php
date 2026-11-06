<?php

namespace Tests\Traits;

use App\Services\Utilities\HostFilesystem;
use Closure;

/**
 * Swaps the HostFilesystem OS seam for a fake, so ownership code can be tested
 * without running as root. mkdir, chmod and is_dir stay real (tests work in a
 * temp dir); os-release, the root and Docker checks, chown and chgrp are faked
 * and every ownership call is recorded. The process list and user names are
 * faked too, and file_exists and is_dir can be, through the public properties.
 * lstat, file reads and writes, deletes, directory listings, realpath and
 * crontab reads are always faked: a path the test did not put in place does
 * not exist, so a test never reads or changes the real /etc.
 */
trait FakesHostFilesystem
{
    /**
     * @param  Closure(string): bool|null  $beforeMakeDirectory  return false to make that mkdir fail
     */
    protected function fakeHostFilesystem(
        ?string $osRelease = "NAME=\"Rocky Linux\"\nID=\"rocky\"\n",
        bool $ownershipSucceeds = true,
        bool $isRoot = true,
        ?Closure $beforeMakeDirectory = null,
        bool $isDocker = false,
    ): HostFilesystem {
        $fake = new class($osRelease, $ownershipSucceeds, $isRoot, $beforeMakeDirectory, $isDocker) extends HostFilesystem
        {
            /** @var list<array{path: string, user: string}> */
            public array $chowned = [];

            /** @var list<array{path: string, group: string}> */
            public array $chgrped = [];

            /** @var list<array{path: string, mode: int}> */
            public array $chmodded = [];

            /** @var list<array{pid: int, uid: int, arguments: list<string>}> */
            public array $processList = [];

            /** @var list<string>|null paths that exist; null checks the real filesystem */
            public ?array $existingPaths = null;

            /** @var array<int, string> */
            public array $userNames = [0 => 'root'];

            /** @var array<string, array{uid: int, gid: int, mode: int, type: string}> lstat results by path; anything else does not exist */
            public array $pathStats = [];

            /** @var array<string, string> file contents by path; anything else is unreadable */
            public array $fileContents = [];

            /** @var array<string, string> realpath results by path; anything else does not resolve */
            public array $resolvedPaths = [];

            /** @var array<string, array{exitCode: int, output: string, errorOutput: string}> crontab -l results by user; anything else has no crontab */
            public array $crontabs = [];

            /** @var list<string> users whose crontab was read */
            public array $crontabReads = [];

            /** @var list<string>|null directories that exist; null checks the real filesystem */
            public ?array $directories = null;

            /** @var list<string> paths written, in order */
            public array $writes = [];

            /** @var list<string> paths deleted, in order */
            public array $deletes = [];

            public function __construct(
                private ?string $osRelease,
                private bool $ownershipSucceeds,
                private bool $isRoot,
                private ?Closure $beforeMakeDirectory,
                private bool $isDocker,
            ) {}

            public function readOsRelease(): ?string
            {
                return $this->osRelease;
            }

            public function isRoot(): bool
            {
                return $this->isRoot;
            }

            public function isDocker(): bool
            {
                return $this->isDocker;
            }

            public function makeDirectory(string $path, int $mode): bool
            {
                if ($this->beforeMakeDirectory !== null && ($this->beforeMakeDirectory)($path) === false) {
                    return false;
                }

                return parent::makeDirectory($path, $mode);
            }

            public function chmod(string $path, int $mode): bool
            {
                $this->chmodded[] = ['path' => $path, 'mode' => $mode];

                // A faked file has no real counterpart to chmod.
                if (array_key_exists($path, $this->fileContents)) {
                    $this->pathStats[$path]['mode'] = $mode;

                    return true;
                }

                return parent::chmod($path, $mode);
            }

            public function isDirectory(string $path): bool
            {
                return $this->directories === null
                    ? parent::isDirectory($path)
                    : in_array($path, $this->directories, true);
            }

            /**
             * Writes only to the fake. Requires faked paths ($existingPaths) so
             * fileExists sees the new file.
             */
            public function writeFile(string $path, string $contents): bool
            {
                $this->writes[] = $path;
                $this->fileContents[$path] = $contents;
                $this->pathStats[$path] ??= ['uid' => 0, 'gid' => 0, 'mode' => 0644, 'type' => 'file'];

                if ($this->existingPaths !== null && ! in_array($path, $this->existingPaths, true)) {
                    $this->existingPaths[] = $path;
                }

                return true;
            }

            public function deleteFile(string $path): bool
            {
                $this->deletes[] = $path;
                $existed = array_key_exists($path, $this->fileContents);
                unset($this->fileContents[$path], $this->pathStats[$path]);

                if ($this->existingPaths !== null) {
                    $this->existingPaths = array_values(array_diff($this->existingPaths, [$path]));
                }

                return $existed;
            }

            /**
             * Faked files, existing paths and symlinks directly inside $directory.
             */
            public function listDirectory(string $directory): array
            {
                $paths = array_unique([...array_keys($this->fileContents), ...($this->existingPaths ?? []), ...array_keys($this->resolvedPaths)]);

                return array_values(array_filter($paths, fn (string $path): bool => dirname($path) === $directory));
            }

            public function chown(string $path, string $user): bool
            {
                $this->chowned[] = ['path' => $path, 'user' => $user];

                return $this->ownershipSucceeds;
            }

            public function chgrp(string $path, string $group): bool
            {
                $this->chgrped[] = ['path' => $path, 'group' => $group];

                return $this->ownershipSucceeds;
            }

            public function fileExists(string $path): bool
            {
                return $this->existingPaths === null
                    ? parent::fileExists($path)
                    : in_array($path, $this->existingPaths, true);
            }

            public function linkStat(string $path): ?array
            {
                return $this->pathStats[$path] ?? null;
            }

            public function readFile(string $path): ?string
            {
                return $this->fileContents[$path] ?? null;
            }

            public function resolvePath(string $path): ?string
            {
                return $this->resolvedPaths[$path] ?? null;
            }

            public function readCrontab(string $user): array
            {
                $this->crontabReads[] = $user;

                return $this->crontabs[$user] ?? ['exitCode' => 1, 'output' => '', 'errorOutput' => "no crontab for {$user}\n"];
            }

            /**
             * Puts a root:root 0644 regular file in place, or whatever stat is given.
             *
             * @param  array{uid?: int, gid?: int, mode?: int, type?: string}  $stat
             */
            public function putFile(string $path, string $contents, array $stat = []): void
            {
                $this->pathStats[$path] = array_merge(['uid' => 0, 'gid' => 0, 'mode' => 0644, 'type' => 'file'], $stat);
                $this->fileContents[$path] = $contents;
            }

            public function processes(): array
            {
                return $this->processList;
            }

            public function userName(int $uid): ?string
            {
                return $this->userNames[$uid] ?? null;
            }

            /**
             * @return list<string>
             */
            public function chownedPaths(): array
            {
                return array_column($this->chowned, 'path');
            }
        };

        app()->instance(HostFilesystem::class, $fake);

        return $fake;
    }

    /**
     * A fake Rocky Linux box where rconfig:check-worker-user finds nothing
     * wrong: Horizon runs as apache, and the cron.d file and Supervisor config
     * are root:root 0644 and run as apache. Tests then break one thing.
     */
    protected function fakeHealthyWorkerBox(bool $isRoot = false, bool $isDocker = false): HostFilesystem
    {
        $host = $this->fakeHostFilesystem(isRoot: $isRoot, isDocker: $isDocker);
        $app = base_path();

        $host->userNames = [0 => 'root', 48 => 'apache'];
        $host->processList = [
            ['pid' => 4321, 'uid' => 48, 'arguments' => ['php', "{$app}/artisan", 'horizon']],
        ];
        $host->putFile('/etc/cron.d/rconfig-core', implode("\n", [
            '# rConfig V8 Core scheduler. Written by setup_cron.sh.',
            'SHELL=/bin/sh',
            "* * * * * apache cd {$app} && php artisan schedule:run >> /dev/null 2>&1",
            '',
        ]));
        $host->putFile('/etc/supervisord.d/rconfig-core-horizon.ini', implode("\n", [
            '[program:rconfig-core-horizon]',
            "command=php {$app}/artisan horizon",
            'autostart=true',
            'user=apache',
            '',
        ]));

        return $host;
    }
}
