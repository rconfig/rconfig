<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

beforeEach(function () {
    $this->tempRoot = storage_path('framework/testing/custom_chown_' . getmypid() . '_' . uniqid());
    File::ensureDirectoryExists($this->tempRoot . '/child/grandchild');
    touch($this->tempRoot . '/child/file.txt');
});

afterEach(function () {
    File::deleteDirectory($this->tempRoot);
});

test('chowns and chgrps the path to the web user', function () {
    $fake = $this->fakeHostFilesystem(osRelease: "NAME=\"Ubuntu\"\nID=ubuntu\n");

    custom_chown($this->tempRoot);

    expect($fake->chowned)->toBe([['path' => $this->tempRoot, 'user' => 'www-data']]);
    expect($fake->chgrped)->toBe([['path' => $this->tempRoot, 'group' => 'www-data']]);
});

test('recursive walks the whole tree', function () {
    $fake = $this->fakeHostFilesystem();

    custom_chown($this->tempRoot, recursive: true);

    $paths = $fake->chownedPaths();
    sort($paths);

    expect($paths)->toBe([
        $this->tempRoot,
        $this->tempRoot . '/child',
        $this->tempRoot . '/child/file.txt',
        $this->tempRoot . '/child/grandchild',
    ]);
});

test('a failed chown as root logs a warning with the path and target user, and does not throw', function () {
    Log::spy();
    $this->fakeHostFilesystem(ownershipSucceeds: false, isRoot: true);

    custom_chown($this->tempRoot);

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message): bool => str_contains($message, $this->tempRoot) && str_contains($message, 'apache'));
});

test('a failed chown when not root is silent', function () {
    Log::spy();
    $this->fakeHostFilesystem(ownershipSucceeds: false, isRoot: false);

    custom_chown($this->tempRoot);

    Log::shouldNotHaveReceived('warning');
});

test('a successful chown as root logs nothing', function () {
    Log::spy();
    $this->fakeHostFilesystem(ownershipSucceeds: true, isRoot: true);

    custom_chown($this->tempRoot);

    Log::shouldNotHaveReceived('warning');
});
