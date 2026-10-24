<?php

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * The suite runs as root on the dev box, so setup_cron.sh must never really
 * run from here: it would write /etc/cron.d and rewrite real crontabs. Every
 * test fakes Process and refuses any process that was not faked.
 */
beforeEach(function () {
    Process::preventStrayProcesses();
});

test('runs setup_cron.sh from the app folder when root', function () {
    $this->fakeHostFilesystem(isRoot: true);
    Process::fake(['*' => Process::result("setup_cron: wrote /etc/cron.d/rconfig-core\n")]);

    $this->artisan('rconfig:setup-cron')
        ->expectsOutputToContain('setup_cron: wrote /etc/cron.d/rconfig-core')
        ->expectsOutputToContain('Scheduler cron file installed')
        ->assertExitCode(0);

    Process::assertRanTimes(function (PendingProcess $process): bool {
        return $process->command === ['bash', base_path('setup_cron.sh')]
            && $process->path === base_path();
    }, 1);
});

test('fails and says how to retry when setup_cron.sh fails', function () {
    $this->fakeHostFilesystem(isRoot: true);
    Process::fake(['*' => Process::result(errorOutput: "setup_cron: ERROR: web user 'apache' does not exist\n", exitCode: 1)]);

    $this->artisan('rconfig:setup-cron')
        ->expectsOutputToContain("web user 'apache' does not exist")
        ->expectsOutputToContain('sudo ./setup_cron.sh')
        ->assertExitCode(1);
});

test('prints the sudo command and runs nothing when not root', function () {
    $this->fakeHostFilesystem(isRoot: false);
    Process::fake();

    $this->artisan('rconfig:setup-cron')
        ->expectsOutputToContain('needs root')
        ->expectsOutputToContain('sudo ./setup_cron.sh')
        ->assertExitCode(0);

    Process::assertNothingRan();
});

test('skips in Docker and runs nothing', function () {
    $this->fakeHostFilesystem(isRoot: true, isDocker: true);
    Process::fake();

    $this->artisan('rconfig:setup-cron')
        ->expectsOutputToContain('Running in Docker')
        ->assertExitCode(0);

    Process::assertNothingRan();
});

test('no application code writes to a crontab directly', function () {
    // setup_cron.sh is the single place a crontab is changed. The old
    // installer wrote the scheduler into whichever user's crontab ran it.
    // A crontab call is a string that starts with the binary, as in
    // ['crontab', ...] or "crontab -". The only one allowed is the read-only
    // crontab -u <user> -l in HostFilesystem, used by rconfig:check-worker-user.
    $calls = collect(File::allFiles(app_path()))
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all('/^.*[\'"]crontab[\'"\s].*$/m', File::get($file->getPathname()), $matches);

            return array_map(fn (string $line): string => $file->getRelativePathname() . ': ' . trim($line), $matches[0]);
        })
        ->values()
        ->all();

    expect($calls)->toBe([
        "Services/Utilities/HostFilesystem.php: \$result = Process::timeout(30)->run(['crontab', '-u', \$user, '-l']);",
    ]);
});
