<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Drives setup_cron.sh against a throwaway app folder, cron folder and set of
 * crontabs. RCONFIG_CRON_DIR puts the script in test mode, and RCONFIG_CRONTAB
 * points at a fake crontab that keeps each user's crontab as a file in the
 * sandbox, so the real crontabs and /etc/cron.d are never read or written.
 * RCONFIG_PHP points at a stub that prints FAKE_WEB_USER in place of artisan
 * rconfig:web-user. "nobody" is the web user because it exists on RHEL, Ubuntu
 * and the CI runner. RCONFIG_OS_RELEASE points at a Rocky 9 os-release in the
 * sandbox, so the result never depends on the OS the suite runs on.
 */
beforeEach(function () {
    $root = sys_get_temp_dir() . '/rconfig-setup-cron-' . bin2hex(random_bytes(6));

    $this->sandbox = [
        'root' => $root,
        'app' => "{$root}/app",
        'cron' => "{$root}/cron.d",
        'crontabs' => "{$root}/crontabs",
        'backups' => "{$root}/backups",
        'php' => "{$root}/fake-php",
        'crontab' => "{$root}/fake-crontab",
        'os-release' => "{$root}/os-release",
    ];

    foreach (['app', 'cron', 'crontabs', 'backups'] as $dir) {
        File::makeDirectory($this->sandbox[$dir], 0755, true);
    }

    File::copy(base_path('setup_cron.sh'), "{$this->sandbox['app']}/setup_cron.sh");
    chmod("{$this->sandbox['app']}/setup_cron.sh", 0755);
    File::put($this->sandbox['os-release'], "NAME=\"Rocky Linux\"\nID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n");

    File::put($this->sandbox['php'], "#!/bin/sh\nprintf '%s\\n' \"\$FAKE_WEB_USER\"\nexit \"\${FAKE_PHP_EXIT:-0}\"\n");
    chmod($this->sandbox['php'], 0755);

    // Fake crontab: "-u USER -l" prints crontabs/USER, "-u USER FILE" replaces
    // it. Every call is logged so tests can tell whether a crontab was rewritten.
    File::put($this->sandbox['crontab'], <<<SH
        #!/bin/sh
        echo "\$*" >> '{$root}/crontab-calls'
        [ "\$1" = "-u" ] || { echo "fake crontab: expected -u" >&2; exit 2; }
        if [ "\$3" = "-l" ]; then
            if [ -n "\${FAKE_CRONTAB_LIST_FAIL:-}" ]; then echo "crontab: cannot open spool" >&2; exit 2; fi
            if [ -f '{$this->sandbox['crontabs']}'/"\$2" ]; then cat '{$this->sandbox['crontabs']}'/"\$2"; exit 0; fi
            echo "no crontab for \$2" >&2
            exit 1
        fi
        if [ "\$2" = "\${FAKE_CRONTAB_INSTALL_FAIL_FOR:-}" ]; then echo "crontab: install failed" >&2; exit 1; fi
        cp "\$3" '{$this->sandbox['crontabs']}'/"\$2"
        SH);
    chmod($this->sandbox['crontab'], 0755);

    // Safety net: if the script ever ignores RCONFIG_CRONTAB or test mode, it
    // must not reach the host's real crontab, cron or SELinux tools. These
    // fakes shadow the real binaries, record the call and fail, and afterEach
    // asserts they were never run.
    File::makeDirectory("{$root}/bin");
    foreach (['crontab', 'systemctl', 'restorecon'] as $binary) {
        File::put("{$root}/bin/{$binary}", "#!/bin/sh\necho \"{$binary} \$*\" >> '{$root}/host-calls'\nexit 99\n");
        chmod("{$root}/bin/{$binary}", 0755);
    }
});

afterEach(function () {
    $hostCalls = "{$this->sandbox['root']}/host-calls";
    $calls = file_exists($hostCalls) ? File::get($hostCalls) : '';

    File::deleteDirectory($this->sandbox['root']);

    expect($calls)->toBe('', 'setup_cron.sh called a real host binary while in test mode.');
});

/**
 * @param  array<string, string>  $sandbox
 * @param  array<string, string>  $env
 */
function runSetupCron(array $sandbox, string $webUser = 'nobody', array $env = [], ?string $script = null): ProcessResult
{
    return Process::env(array_merge([
        'RCONFIG_CRON_DIR' => $sandbox['cron'],
        'RCONFIG_CRONTAB' => $sandbox['crontab'],
        'RCONFIG_CRONTAB_BACKUP_DIR' => $sandbox['backups'],
        'RCONFIG_PHP' => $sandbox['php'],
        'RCONFIG_OS_RELEASE' => $sandbox['os-release'],
        'FAKE_WEB_USER' => $webUser,
        'PATH' => "{$sandbox['root']}/bin:" . getenv('PATH'),
    ], $env))->run([$script ?? "{$sandbox['app']}/setup_cron.sh"]);
}

/**
 * @param  array<string, string>  $sandbox
 */
function putCrontab(array $sandbox, string $user, string $contents): void
{
    File::put("{$sandbox['crontabs']}/{$user}", $contents);
}

/**
 * @param  array<string, string>  $sandbox
 */
function crontabOf(array $sandbox, string $user): ?string
{
    $path = "{$sandbox['crontabs']}/{$user}";

    return file_exists($path) ? File::get($path) : null;
}

/**
 * Crontab rewrites the script made, as "-u USER FILE" calls to the fake.
 *
 * @param  array<string, string>  $sandbox
 * @return list<string>
 */
function crontabInstalls(array $sandbox): array
{
    $log = "{$sandbox['root']}/crontab-calls";
    $calls = file_exists($log) ? file($log, FILE_IGNORE_NEW_LINES) : [];

    return array_values(array_filter($calls, fn (string $call): bool => ! str_ends_with($call, ' -l')));
}

/**
 * @return list<string>
 */
function dirEntries(string $dir): array
{
    return array_values(array_diff(scandir($dir) ?: [], ['.', '..']));
}

/**
 * @param  array<string, string>  $sandbox
 */
function coreSchedulerLine(array $sandbox): string
{
    return "* * * * * cd {$sandbox['app']} && php artisan schedule:run >> /dev/null 2>&1";
}

test('writes rconfig-core with the web user, the app folder and a trailing newline', function () {
    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput());

    $target = "{$this->sandbox['cron']}/rconfig-core";
    $contents = File::get($target);

    expect(dirEntries($this->sandbox['cron']))->toBe(['rconfig-core'])
        ->and(basename($target))->not->toContain('.')
        ->and(is_link($target))->toBeFalse()
        ->and(fileperms($target) & 0777)->toBe(0644)
        ->and($contents)->toEndWith("\n")
        ->and($contents)->toContain("\nSHELL=/bin/sh\n")
        ->and($contents)->toContain("\nPATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n")
        ->and($contents)->toContain("\n* * * * * nobody cd {$this->sandbox['app']} && php artisan schedule:run >> /dev/null 2>&1\n")
        ->and($contents)->not->toMatch('/^\S+ \S+ \S+ \S+ \S+ root /m');
});

test('the cron file is owned by root with mode 0644 when run as root', function () {
    if (! function_exists('posix_geteuid') || posix_geteuid() !== 0) {
        $this->markTestSkipped('Real ownership can only be checked when the suite runs as root.');
    }

    $result = runSetupCron($this->sandbox);
    $target = "{$this->sandbox['cron']}/rconfig-core";

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(fileowner($target))->toBe(0)
        ->and(filegroup($target))->toBe(0)
        ->and(fileperms($target) & 0777)->toBe(0644);
});

test('removes the CORE scheduler line from root\'s crontab and keeps every other line', function () {
    $original = "MAILTO=ops@example.com\n"
        . "# nightly certbot\n"
        . "0 3 * * * certbot renew --quiet\n"
        . coreSchedulerLine($this->sandbox) . "\n"
        . '# ' . coreSchedulerLine($this->sandbox) . "\n"
        . "\n"
        . "15 * * * * /usr/local/bin/backup.sh\n";
    putCrontab($this->sandbox, 'root', $original);

    $result = runSetupCron($this->sandbox);

    $backups = File::glob("{$this->sandbox['backups']}/crontab-root.rconfig-bak-*");

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe(
            "MAILTO=ops@example.com\n"
            . "# nightly certbot\n"
            . "0 3 * * * certbot renew --quiet\n"
            . '# ' . coreSchedulerLine($this->sandbox) . "\n"
            . "\n"
            . "15 * * * * /usr/local/bin/backup.sh\n"
        )
        ->and($backups)->toHaveCount(1)
        ->and(File::get($backups[0]))->toBe($original)
        ->and(fileperms($backups[0]) & 0777)->toBe(0600)
        ->and($result->output())->toContain('removed this install\'s scheduler line from the crontab for root')
        ->and(is_file("{$this->sandbox['cron']}/rconfig-core"))->toBeTrue();
});

test('leaves a PRO scheduler line in root\'s crontab alone and does not rewrite it', function () {
    // app8 shares a prefix with the sandbox app folder, so a plain prefix
    // match would wrongly claim it.
    $original = "* * * * * cd {$this->sandbox['app']}8 && php artisan schedule:run >> /dev/null 2>&1\n"
        . "* * * * * php {$this->sandbox['app']}8/current/artisan schedule:run >> /dev/null 2>&1\n";
    putCrontab($this->sandbox, 'root', $original);

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe($original)
        ->and(crontabInstalls($this->sandbox))->toBe([])
        ->and(File::glob("{$this->sandbox['backups']}/*"))->toBe([]);
});

test('removes only the CORE line when root\'s crontab has both CORE and PRO lines', function () {
    $proLine = "* * * * * cd {$this->sandbox['app']}8 && php artisan schedule:run >> /dev/null 2>&1";
    putCrontab($this->sandbox, 'root', coreSchedulerLine($this->sandbox) . "\n{$proLine}\n");

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe("{$proLine}\n");
});

test('removes the CORE scheduler line from the web user\'s crontab and keeps a PRO line there', function () {
    $proLine = "* * * * * php {$this->sandbox['app']}8/artisan schedule:run >> /dev/null 2>&1";
    $original = "# added by hand, per the hardening guide\n"
        . coreSchedulerLine($this->sandbox) . "\n"
        . "{$proLine}\n";
    putCrontab($this->sandbox, 'nobody', $original);

    $result = runSetupCron($this->sandbox);

    $backups = File::glob("{$this->sandbox['backups']}/crontab-nobody.rconfig-bak-*");

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'nobody'))->toBe("# added by hand, per the hardening guide\n{$proLine}\n")
        ->and($backups)->toHaveCount(1)
        ->and(File::get($backups[0]))->toBe($original)
        ->and(dirEntries($this->sandbox['cron']))->toBe(['rconfig-core'])
        ->and(crontabInstalls($this->sandbox))->toHaveCount(1)
        ->and(crontabInstalls($this->sandbox)[0])->toStartWith('-u nobody ');
});

test('matches other ways of writing the CORE scheduler line', function (string $line) {
    putCrontab($this->sandbox, 'root', "{$line}\n0 3 * * * certbot renew\n");

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe("0 3 * * * certbot renew\n");
})->with([
    'artisan by full path' => fn () => "* * * * * php {$this->sandbox['app']}/artisan schedule:run >> /dev/null 2>&1",
    'php by full path' => fn () => "* * * * * /usr/bin/php {$this->sandbox['app']}/artisan schedule:run",
    'quoted folder with trailing slash' => fn () => "* * * * * cd \"{$this->sandbox['app']}/\" && php artisan schedule:run",
    'cd joined to &&' => fn () => "* * * * * cd {$this->sandbox['app']}&& php artisan schedule:run",
    'single quoted folder' => fn () => "* * * * * cd '{$this->sandbox['app']}' && php artisan schedule:run",
]);

test('keeps lines that mention the app folder but do not run the scheduler', function () {
    $original = "0 4 * * * cd {$this->sandbox['app']} && php artisan rconfig:clear-all\n"
        . "* * * * * cd /srv{$this->sandbox['app']} && php artisan schedule:run\n";
    putCrontab($this->sandbox, 'root', $original);

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe($original)
        ->and(crontabInstalls($this->sandbox))->toBe([]);
});

test('removes a line written with the app folder reached through a symlink', function () {
    $link = "{$this->sandbox['root']}/rconfig-link";
    symlink($this->sandbox['app'], $link);
    putCrontab($this->sandbox, 'root', "* * * * * cd {$link} && php artisan schedule:run >> /dev/null 2>&1\n");

    $result = runSetupCron($this->sandbox, script: "{$link}/setup_cron.sh");

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(crontabOf($this->sandbox, 'root'))->toBe('')
        ->and(File::get("{$this->sandbox['cron']}/rconfig-core"))->toContain("nobody cd {$this->sandbox['app']} && php artisan schedule:run");
});

test('fails with nothing written when the web user is not usable', function (string $webUser, array $env, string $message) {
    $rootCrontab = coreSchedulerLine($this->sandbox) . "\n";
    putCrontab($this->sandbox, 'root', $rootCrontab);

    $result = runSetupCron($this->sandbox, $webUser, $env);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain($message)
        ->and(dirEntries($this->sandbox['cron']))->toBe([])
        ->and(crontabOf($this->sandbox, 'root'))->toBe($rootCrontab)
        ->and(crontabInstalls($this->sandbox))->toBe([])
        ->and(File::glob("{$this->sandbox['backups']}/*"))->toBe([]);
})->with([
    'missing user' => ['rconfig_no_such_user', [], "web user 'rconfig_no_such_user' does not exist"],
    'uid 0' => ['root', [], 'must never run as root'],
    'invalid name' => ['Bad User!', [], 'not a valid user name'],
    'artisan failure' => ['nobody', ['FAKE_PHP_EXIT' => '1'], 'could not detect the web user'],
]);

test('is safe to run twice and gives the same result', function () {
    putCrontab($this->sandbox, 'root', coreSchedulerLine($this->sandbox) . "\n0 3 * * * certbot renew\n");
    putCrontab($this->sandbox, 'nobody', coreSchedulerLine($this->sandbox) . "\n");

    expect(runSetupCron($this->sandbox)->successful())->toBeTrue();

    $cronFile = File::get("{$this->sandbox['cron']}/rconfig-core");
    $rootCrontab = crontabOf($this->sandbox, 'root');
    $installsAfterFirstRun = crontabInstalls($this->sandbox);
    $backupsAfterFirstRun = File::glob("{$this->sandbox['backups']}/*");

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->toContain('is already up to date')
        ->and(File::get("{$this->sandbox['cron']}/rconfig-core"))->toBe($cronFile)
        ->and(substr_count($cronFile, 'schedule:run'))->toBe(1)
        ->and(dirEntries($this->sandbox['cron']))->toBe(['rconfig-core'])
        ->and(crontabOf($this->sandbox, 'root'))->toBe($rootCrontab)
        ->and(crontabOf($this->sandbox, 'nobody'))->toBe('')
        ->and(crontabInstalls($this->sandbox))->toBe($installsAfterFirstRun)
        ->and(File::glob("{$this->sandbox['backups']}/*"))->toBe($backupsAfterFirstRun);
});

test('works when root and the web user have no crontab or an empty one', function (?string $contents) {
    if ($contents !== null) {
        putCrontab($this->sandbox, 'root', $contents);
        putCrontab($this->sandbox, 'nobody', $contents);
    }

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_file("{$this->sandbox['cron']}/rconfig-core"))->toBeTrue()
        ->and(crontabInstalls($this->sandbox))->toBe([])
        ->and(crontabOf($this->sandbox, 'root'))->toBe($contents);
})->with([
    'missing' => [null],
    'empty' => [''],
]);

test('fails with nothing written when a crontab cannot be read', function () {
    $result = runSetupCron($this->sandbox, env: ['FAKE_CRONTAB_LIST_FAIL' => '1']);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('could not read the crontab for root')
        ->and(dirEntries($this->sandbox['cron']))->toBe([])
        ->and(crontabInstalls($this->sandbox))->toBe([]);
});

test('rolls everything back when a crontab cannot be rewritten', function () {
    $rootCrontab = coreSchedulerLine($this->sandbox) . "\n0 3 * * * certbot renew\n";
    $webCrontab = coreSchedulerLine($this->sandbox) . "\n";
    putCrontab($this->sandbox, 'root', $rootCrontab);
    putCrontab($this->sandbox, 'nobody', $webCrontab);

    $result = runSetupCron($this->sandbox, env: ['FAKE_CRONTAB_INSTALL_FAIL_FOR' => 'nobody']);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('could not rewrite the crontab for nobody')
        ->and($result->errorOutput())->toContain('rolled back')
        ->and(dirEntries($this->sandbox['cron']))->toBe([])
        ->and(crontabOf($this->sandbox, 'root'))->toBe($rootCrontab)
        ->and(crontabOf($this->sandbox, 'nobody'))->toBe($webCrontab);
});

test('a rollback puts back the cron file that was there before', function () {
    $target = "{$this->sandbox['cron']}/rconfig-core";
    File::put($target, "# older rconfig-core\n* * * * * nobody true\n");
    chmod($target, 0644);
    putCrontab($this->sandbox, 'root', coreSchedulerLine($this->sandbox) . "\n");

    $result = runSetupCron($this->sandbox, env: ['FAKE_CRONTAB_INSTALL_FAIL_FOR' => 'root']);

    expect($result->exitCode())->not->toBe(0)
        ->and(File::get($target))->toBe("# older rconfig-core\n* * * * * nobody true\n")
        ->and(dirEntries($this->sandbox['cron']))->toBe(['rconfig-core']);
});

test('replaces a cron file with the right content but a mode cron would skip', function () {
    expect(runSetupCron($this->sandbox)->successful())->toBeTrue();
    $target = "{$this->sandbox['cron']}/rconfig-core";
    chmod($target, 0666);

    $result = runSetupCron($this->sandbox);

    clearstatcache();

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->not->toContain('is already up to date')
        ->and(fileperms($target) & 0777)->toBe(0644);
});

test('warns about but keeps another cron file that runs this install\'s scheduler', function () {
    $handMade = "{$this->sandbox['cron']}/rconfig";
    $proFile = "{$this->sandbox['cron']}/rconfig-pro";
    File::put($handMade, "* * * * * root cd {$this->sandbox['app']} && php artisan schedule:run\n");
    File::put($proFile, "* * * * * apache cd {$this->sandbox['app']}8 && php artisan schedule:run\n");

    $result = runSetupCron($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_file($handMade))->toBeTrue()
        ->and($result->output())->toContain("WARNING: {$handMade} also runs the scheduler")
        ->and($result->output())->not->toContain("WARNING: {$proFile}");
});

test('warns about a scheduler line for this install in the system crontab', function () {
    $systemCrontab = "{$this->sandbox['root']}/crontab";
    File::put($systemCrontab, "* * * * * root php {$this->sandbox['app']}/artisan schedule:run\n");

    $result = runSetupCron($this->sandbox, env: ['RCONFIG_SYSTEM_CRONTAB' => $systemCrontab]);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->toContain("WARNING: {$systemCrontab} also runs the scheduler")
        ->and(File::get($systemCrontab))->toContain('schedule:run');
});

test('test mode refuses to run without a fake crontab binary', function () {
    $result = runSetupCron($this->sandbox, env: ['RCONFIG_CRONTAB' => '']);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('RCONFIG_CRON_DIR is set without RCONFIG_CRONTAB')
        ->and(dirEntries($this->sandbox['cron']))->toBe([]);
});

test('detects each supported OS from os-release and picks its cron service', function (string $osRelease, string $id, string $family) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupCron($this->sandbox);

    $service = $family === 'rhel' ? 'crond' : 'cron';

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->toContain("OS: {$id} ({$family} family), cron service {$service}")
        ->and(dirEntries($this->sandbox['cron']))->toBe(['rconfig-core']);
})->with('supported os-release files');

test('refuses an unsupported OS before reading or writing anything', function (string $osRelease) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupCron($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later.')
        ->and(dirEntries($this->sandbox['cron']))->toBe([])
        ->and(file_exists("{$this->sandbox['root']}/crontab-calls"))->toBeFalse();
})->with('unsupported os-release files');

test('refuses Amazon Linux and tells the user to stay on their current version', function () {
    File::put($this->sandbox['os-release'], "NAME=\"Amazon Linux\"\nID=\"amzn\"\nID_LIKE=\"fedora\"\nVERSION_ID=\"2023\"\n");

    $result = runSetupCron($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('Amazon Linux is no longer supported from 8.2.19. Stay on your current version and contact support. Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later.')
        ->and(dirEntries($this->sandbox['cron']))->toBe([])
        ->and(file_exists("{$this->sandbox['root']}/crontab-calls"))->toBeFalse();
});

test('fails without an os-release file rather than guessing the OS', function () {
    File::delete($this->sandbox['os-release']);

    $result = runSetupCron($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain("cannot read {$this->sandbox['os-release']}")
        ->and(dirEntries($this->sandbox['cron']))->toBe([]);
});

test('a missing cron folder says how to install cron on the detected OS', function (string $osRelease, string $hint) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupCron($this->sandbox, env: ['RCONFIG_CRON_DIR' => "{$this->sandbox['root']}/missing"]);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain($hint);
})->with([
    'Rocky' => ["ID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n", 'dnf install cronie, then: systemctl enable --now crond'],
    'Ubuntu' => ["ID=ubuntu\nID_LIKE=debian\n", 'apt install cron, then: systemctl enable --now cron'],
]);

test('a missing crontab command says how to install cron on the detected OS', function () {
    File::put($this->sandbox['os-release'], "ID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n");

    $result = runSetupCron($this->sandbox, env: ['RCONFIG_CRONTAB' => "{$this->sandbox['root']}/no-such-crontab"]);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('not found, so cron is not installed')
        ->and($result->errorOutput())->toContain('dnf install cronie')
        ->and(dirEntries($this->sandbox['cron']))->toBe([]);
});
