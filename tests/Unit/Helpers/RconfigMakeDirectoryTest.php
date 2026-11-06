<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

beforeEach(function () {
    $this->tempRoot = storage_path('framework/testing/make_dir_' . getmypid() . '_' . uniqid());
    File::ensureDirectoryExists($this->tempRoot);
    chmod($this->tempRoot, 0755);
});

afterEach(function () {
    File::deleteDirectory($this->tempRoot);
});

/**
 * The RCO-1317 regression: a recursive mkdir creates missing ancestors in the
 * same call, and the old code chowned only the leaf.
 */
test('every newly created level is chowned and chgrped to the web user, not just the leaf', function () {
    $fake = $this->fakeHostFilesystem();

    $level1 = $this->tempRoot . '/level1';
    $level2 = $level1 . '/level2';
    $level3 = $level2 . '/level3';

    rconfig_make_directory($level3, 0750);

    expect($level3)->toBeDirectory();
    expect($fake->chowned)->toBe([
        ['path' => $level1, 'user' => 'apache'],
        ['path' => $level2, 'user' => 'apache'],
        ['path' => $level3, 'user' => 'apache'],
    ]);
    expect(array_column($fake->chgrped, 'path'))->toBe([$level1, $level2, $level3]);
    expect(array_unique(array_column($fake->chgrped, 'group')))->toBe(['apache']);
    expect($fake->chownedPaths())->not->toContain($this->tempRoot);
});

test('existing levels are left untouched, including their owner and mode', function () {
    $existing = $this->tempRoot . '/existing';
    mkdir($existing);
    chmod($existing, 0700);

    $fake = $this->fakeHostFilesystem();

    $new1 = $existing . '/new1';
    $new2 = $new1 . '/new2';

    rconfig_make_directory($new2, 0750);

    expect($fake->chownedPaths())->toBe([$new1, $new2]);
    expect(array_column($fake->chmodded, 'path'))->toBe([$new1, $new2]);
    expect(makeDirModeOf($existing))->toBe(0700, 'An existing ancestor must keep its mode.');
});

test('re-running on an existing path is a no-op', function () {
    $target = $this->tempRoot . '/a/b/c';

    $this->fakeHostFilesystem();
    rconfig_make_directory($target, 0750);

    clearstatcache();
    $mtimeBefore = filemtime($target);

    $fake = $this->fakeHostFilesystem();
    rconfig_make_directory($target, 0750);
    rconfig_make_directory($target . '/', 0750);

    expect($fake->chowned)->toBe([]);
    expect($fake->chgrped)->toBe([]);
    expect($fake->chmodded)->toBe([]);
    clearstatcache();
    expect(filemtime($target))->toBe($mtimeBefore);
});

test('when not running as root the directories are still created and the failed chown is silent', function () {
    Log::spy();
    $fake = $this->fakeHostFilesystem(ownershipSucceeds: false, isRoot: false);

    $target = $this->tempRoot . '/x/y';

    rconfig_make_directory($target, 0750);

    expect($target)->toBeDirectory();
    expect(makeDirModeOf($this->tempRoot . '/x'))->toBe(0750);
    expect(makeDirModeOf($target))->toBe(0750);
    expect($fake->chownedPaths())->toBe([$this->tempRoot . '/x', $target]);
    Log::shouldNotHaveReceived('warning');
});

test('every created level gets the requested mode regardless of the umask', function () {
    $this->fakeHostFilesystem();
    $originalUmask = umask(0022);

    try {
        $level1 = $this->tempRoot . '/u1';
        $level2 = $level1 . '/u2';
        $level3 = $level2 . '/u3';

        rconfig_make_directory($level3, 0750);

        expect(makeDirModeOf($level1))->toBe(0750);
        expect(makeDirModeOf($level2))->toBe(0750);
        expect(makeDirModeOf($level3))->toBe(0750);
    } finally {
        umask($originalUmask);
    }
});

test('a trailing slash is handled', function () {
    $fake = $this->fakeHostFilesystem();

    rconfig_make_directory($this->tempRoot . '/slash/', 0750);

    expect($this->tempRoot . '/slash')->toBeDirectory();
    expect($fake->chownedPaths())->toBe([$this->tempRoot . '/slash']);
});

test('a level created by another worker mid-call is tolerated and not chowned by this call', function () {
    $level1 = $this->tempRoot . '/race1';
    $level2 = $level1 . '/race2';
    $level3 = $level2 . '/race3';

    // Another worker wins the race for race2: it appears just before our
    // mkdir, so our mkdir fails with the directory already in place.
    $fake = $this->fakeHostFilesystem(beforeMakeDirectory: function (string $path) use ($level2): bool {
        if ($path === $level2) {
            mkdir($path, 0700);

            return false;
        }

        return true;
    });

    rconfig_make_directory($level3, 0750);

    expect($level3)->toBeDirectory();
    expect($fake->chownedPaths())->toBe([$level1, $level3]);
    expect(array_column($fake->chmodded, 'path'))->toBe([$level1, $level3]);
    expect(makeDirModeOf($level2))->toBe(0700, 'The other worker\'s directory must be left as it made it.');
});

test('a level that cannot be created and does not exist throws', function () {
    $this->fakeHostFilesystem(beforeMakeDirectory: fn (string $path): bool => false);

    rconfig_make_directory($this->tempRoot . '/never', 0750);
})->throws(RuntimeException::class, 'Unable to create directory');

/**
 * Real ownership on a real filesystem. Needs root and an existing web user, so
 * it skips cleanly on CI and developer machines.
 */
test('as root every created level is really owned by the web user', function () {
    if (! function_exists('posix_geteuid') || posix_geteuid() !== 0) {
        $this->markTestSkipped('Changing ownership to another user requires running as root.');
    }

    [$webUser] = rconfig_web_user();

    if (posix_getpwnam($webUser) === false) {
        $this->markTestSkipped("Web user '{$webUser}' does not exist on this host.");
    }

    chown($this->tempRoot, 'root');

    $level1 = $this->tempRoot . '/real1';
    $level2 = $level1 . '/real2';
    $level3 = $level2 . '/real3';

    rconfig_make_directory($level3, 0750);

    $ownerOf = function (string $path): string {
        clearstatcache(true, $path);

        return posix_getpwuid(fileowner($path))['name'];
    };

    expect($ownerOf($this->tempRoot))->toBe('root', 'The pre-existing ancestor must be left untouched.');
    expect($ownerOf($level1))->toBe($webUser);
    expect($ownerOf($level2))->toBe($webUser);
    expect($ownerOf($level3))->toBe($webUser);
});

function makeDirModeOf(string $path): int
{
    clearstatcache(true, $path);

    return fileperms($path) & 0777;
}
