<?php

namespace Tests\Traits;

use App\Services\Utilities\HostFilesystem;
use Closure;

/**
 * Swaps the HostFilesystem OS seam for a fake, so ownership code can be tested
 * without running as root. mkdir, chmod and is_dir stay real (tests work in a
 * temp dir); os-release, the root and Docker checks, chown and chgrp are faked
 * and every ownership call is recorded. The process list and user names are
 * faked too, and file_exists can be, through the public properties.
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

                return parent::chmod($path, $mode);
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
}
