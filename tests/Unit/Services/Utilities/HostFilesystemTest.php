<?php

use App\Services\Utilities\HostFilesystem;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * The real read-only methods rconfig:check-worker-user uses, against a temp
 * folder. Nothing outside it is read, and crontab never really runs.
 */
beforeEach(function () {
    $this->dir = sys_get_temp_dir() . '/rconfig-hostfs-' . bin2hex(random_bytes(4));
    mkdir($this->dir, 0755);
    $this->host = new HostFilesystem;
});

afterEach(function () {
    File::deleteDirectory($this->dir);
});

test('linkStat reports a regular file with its permission bits', function () {
    file_put_contents("{$this->dir}/file", 'x');
    chmod("{$this->dir}/file", 0640);

    expect($this->host->linkStat("{$this->dir}/file"))->toMatchArray([
        'uid' => fileowner("{$this->dir}/file"),
        'gid' => filegroup("{$this->dir}/file"),
        'mode' => 0640,
        'type' => 'file',
    ]);
});

test('linkStat reports a symlink as a link and does not follow it', function () {
    file_put_contents("{$this->dir}/target", 'x');
    symlink("{$this->dir}/target", "{$this->dir}/link");

    expect($this->host->linkStat("{$this->dir}/link")['type'])->toBe('link')
        ->and($this->host->linkStat($this->dir)['type'])->toBe('dir')
        ->and($this->host->linkStat("{$this->dir}/missing"))->toBeNull();
});

test('readFile and resolvePath return null for a missing path', function () {
    file_put_contents("{$this->dir}/target", 'contents');
    symlink("{$this->dir}/target", "{$this->dir}/link");

    expect($this->host->readFile("{$this->dir}/target"))->toBe('contents')
        ->and($this->host->readFile("{$this->dir}/missing"))->toBeNull()
        ->and($this->host->resolvePath("{$this->dir}/link"))->toBe(realpath("{$this->dir}/target"))
        ->and($this->host->resolvePath("{$this->dir}/missing"))->toBeNull();
});

test('writeFile, listDirectory and deleteFile work on real files', function () {
    symlink("{$this->dir}/missing-target", "{$this->dir}/dangling");

    expect($this->host->writeFile("{$this->dir}/vhost.conf", 'contents'))->toBeTrue()
        ->and(file_get_contents("{$this->dir}/vhost.conf"))->toBe('contents')
        ->and($this->host->listDirectory($this->dir))->toEqualCanonicalizing(["{$this->dir}/dangling", "{$this->dir}/vhost.conf"])
        ->and($this->host->listDirectory("{$this->dir}/missing"))->toBe([])
        ->and($this->host->deleteFile("{$this->dir}/vhost.conf"))->toBeTrue()
        ->and(file_exists("{$this->dir}/vhost.conf"))->toBeFalse()
        ->and($this->host->deleteFile("{$this->dir}/vhost.conf"))->toBeFalse()
        ->and($this->host->writeFile("{$this->dir}/missing/vhost.conf", 'x'))->toBeFalse();
});

test('readCrontab only ever lists a crontab', function () {
    Process::preventStrayProcesses();
    Process::fake(['*' => Process::result(errorOutput: "no crontab for root\n", exitCode: 1)]);

    expect($this->host->readCrontab('root'))->toBe([
        'exitCode' => 1,
        'output' => '',
        'errorOutput' => "no crontab for root\n",
    ]);

    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['crontab', '-u', 'root', '-l']);
});
