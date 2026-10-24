<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Drives setup_supervisor.sh against a throwaway app folder and Supervisor
 * config folder. RCONFIG_SUPERVISOR_DIR puts the script in test mode, so it
 * never reloads Supervisor or touches /etc. RCONFIG_PHP points at a stub that
 * prints FAKE_WEB_USER in place of artisan rconfig:web-user. "nobody" is used
 * as the web user because it exists on RHEL, Ubuntu and the CI runner.
 * RCONFIG_OS_RELEASE points at a Rocky 9 os-release in the sandbox, so the
 * result never depends on the OS the suite runs on.
 */
const SETUP_SUPERVISOR_STUB_PLACEHOLDER = 'user=RCONFIG_WEB_USER';

beforeEach(function () {
    $root = sys_get_temp_dir() . '/rconfig-setup-supervisor-' . bin2hex(random_bytes(6));

    $this->sandbox = [
        'root' => $root,
        'app' => "{$root}/app",
        'sup' => "{$root}/supervisor",
        'php' => "{$root}/fake-php",
        'os-release' => "{$root}/os-release",
    ];

    File::makeDirectory($this->sandbox['app'], 0755, true);
    File::makeDirectory($this->sandbox['sup'], 0755, true);

    File::copy(base_path('setup_supervisor.sh'), "{$this->sandbox['app']}/setup_supervisor.sh");
    chmod("{$this->sandbox['app']}/setup_supervisor.sh", 0755);
    File::copy(base_path('horizon_supervisor.ini'), "{$this->sandbox['app']}/horizon_supervisor.ini");
    File::put($this->sandbox['os-release'], "NAME=\"Rocky Linux\"\nID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n");

    File::put($this->sandbox['php'], "#!/bin/sh\nprintf '%s\\n' \"\$FAKE_WEB_USER\"\nexit \"\${FAKE_PHP_EXIT:-0}\"\n");
    chmod($this->sandbox['php'], 0755);

    // Safety net: if the script ever ignores RCONFIG_SUPERVISOR_DIR it must not
    // reload the host's real Supervisor. These fakes shadow the real binaries,
    // record the call and fail, and afterEach asserts they were never run.
    File::makeDirectory("{$root}/bin");
    foreach (['systemctl', 'supervisorctl'] as $binary) {
        File::put("{$root}/bin/{$binary}", "#!/bin/sh\necho \"{$binary} \$*\" >> '{$root}/host-calls'\nexit 99\n");
        chmod("{$root}/bin/{$binary}", 0755);
    }
});

afterEach(function () {
    $hostCalls = "{$this->sandbox['root']}/host-calls";
    $calls = file_exists($hostCalls) ? File::get($hostCalls) : '';

    File::deleteDirectory($this->sandbox['root']);

    expect($calls)->toBe('', 'setup_supervisor.sh tried to reload the real Supervisor while in test mode.');
});

/**
 * @param  array{root: string, app: string, sup: string, php: string, os-release: string}  $sandbox
 * @param  array<string, string>  $env
 */
function runSetupSupervisor(array $sandbox, string $webUser = 'nobody', array $env = []): ProcessResult
{
    return Process::env(array_merge([
        'RCONFIG_SUPERVISOR_DIR' => $sandbox['sup'],
        'RCONFIG_SUPERVISOR_EXT' => 'ini',
        'RCONFIG_PHP' => $sandbox['php'],
        'RCONFIG_OS_RELEASE' => $sandbox['os-release'],
        'FAKE_WEB_USER' => $webUser,
        'PATH' => "{$sandbox['root']}/bin:" . getenv('PATH'),
    ], $env))->run(["{$sandbox['app']}/setup_supervisor.sh"]);
}

/**
 * @return list<string>
 */
function supervisorDirEntries(string $dir): array
{
    return array_values(array_diff(scandir($dir) ?: [], ['.', '..']));
}

test('writes the CORE file name and program name with the user and app folder filled in', function (string $extension) {
    $result = runSetupSupervisor($this->sandbox, env: ['RCONFIG_SUPERVISOR_EXT' => $extension]);

    expect($result->successful())->toBeTrue($result->errorOutput());

    $target = "{$this->sandbox['sup']}/rconfig-core-horizon.{$extension}";
    $rendered = File::get($target);

    expect(is_link($target))->toBeFalse()
        ->and(is_file($target))->toBeTrue()
        ->and(fileperms($target) & 0777)->toBe(0644)
        ->and($rendered)->toContain("\n[program:rconfig-core-horizon]\n")
        ->and($rendered)->not->toContain('[program:horizon]')
        ->and($rendered)->toContain("\nuser=nobody\n")
        ->and($rendered)->toContain("command=php {$this->sandbox['app']}/artisan horizon")
        ->and($rendered)->toContain("stdout_logfile={$this->sandbox['app']}/storage/logs/horizon.log")
        ->and($rendered)->not->toContain('RCONFIG_')
        ->and($rendered)->not->toMatch('/^user\s*=\s*root\s*$/mi')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(["rconfig-core-horizon.{$extension}"]);
})->with(['ini', 'conf']);

test('removes an old CORE symlink under the old name and leaves the stub alone', function (string $extension) {
    $stub = "{$this->sandbox['app']}/horizon_supervisor.ini";
    $stubBefore = File::get($stub);
    $old = "{$this->sandbox['sup']}/horizon_supervisor.{$extension}";
    symlink($stub, $old);

    $result = runSetupSupervisor($this->sandbox, env: ['RCONFIG_SUPERVISOR_EXT' => $extension]);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_link($old))->toBeFalse()
        ->and(file_exists($old))->toBeFalse()
        ->and(File::get($stub))->toBe($stubBefore)
        ->and($result->output())->toContain("removed old symlink {$old}")
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(["rconfig-core-horizon.{$extension}"]);
})->with(['ini', 'conf']);

test('removes an old regular file under the old name when its command runs from this app folder', function () {
    $old = "{$this->sandbox['sup']}/horizon_supervisor.ini";
    File::put($old, "[program:horizon]\ncommand=php {$this->sandbox['app']}/artisan horizon\nuser=root\nnumprocs=4\n");

    $result = runSetupSupervisor($this->sandbox);

    $backups = File::glob("{$old}.rconfig-bak-*");

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(file_exists($old))->toBeFalse()
        ->and($backups)->toHaveCount(1)
        ->and(File::get($backups[0]))->toContain('numprocs=4')
        ->and(is_file("{$this->sandbox['sup']}/rconfig-core-horizon.ini"))->toBeTrue();
});

test('leaves a PRO style horizon_supervisor.ini that runs from another folder untouched', function () {
    // app8 shares a prefix with the sandbox app folder, so a plain prefix
    // match would wrongly claim it.
    $old = "{$this->sandbox['sup']}/horizon_supervisor.ini";
    $proConfig = "[program:horizon]\ncommand=php {$this->sandbox['app']}8/artisan horizon\nuser=apache\nstdout_logfile={$this->sandbox['app']}/storage/logs/horizon.log\n";
    File::put($old, $proConfig);
    $mtimeBefore = filemtime($old);

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_file($old))->toBeTrue()
        ->and(File::get($old))->toBe($proConfig)
        ->and(filemtime($old))->toBe($mtimeBefore)
        ->and(File::glob("{$old}.rconfig-bak-*"))->toBe([])
        ->and($result->output())->toContain("note: left {$old} alone")
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(['horizon_supervisor.ini', 'rconfig-core-horizon.ini']);
});

test('leaves an old-name symlink that points outside this app folder untouched', function () {
    $elsewhere = "{$this->sandbox['root']}/pro-horizon.ini";
    File::put($elsewhere, "[program:horizon]\ncommand=php /var/www/html/rconfig8/artisan horizon\n");
    $old = "{$this->sandbox['sup']}/horizon_supervisor.ini";
    symlink($elsewhere, $old);

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_link($old))->toBeTrue()
        ->and(readlink($old))->toBe($elsewhere)
        ->and($result->output())->toContain("note: left {$old} alone");
});

test('removes other symlinks into the app folder but not symlinks elsewhere', function () {
    $elsewhere = "{$this->sandbox['root']}/other-app.ini";
    File::put($elsewhere, "[program:other]\n");
    symlink("{$this->sandbox['app']}/horizon_supervisor.ini", "{$this->sandbox['sup']}/hand_made_horizon.ini");
    symlink($elsewhere, "{$this->sandbox['sup']}/other_app.ini");

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(file_exists("{$this->sandbox['sup']}/hand_made_horizon.ini"))->toBeFalse()
        ->and(is_link("{$this->sandbox['sup']}/other_app.ini"))->toBeTrue();
});

test('warns about but keeps a hand made file under another name that runs this install', function () {
    $handMade = "{$this->sandbox['sup']}/horizon_supervisor_v8core.ini";
    File::put($handMade, "[program:v8core_horizon]\ncommand=php {$this->sandbox['app']}/artisan horizon\n");

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(is_file($handMade))->toBeTrue()
        ->and($result->output())->toContain("WARNING: {$handMade} also runs a program from {$this->sandbox['app']}");
});

test('is safe to run twice and does not back up an unchanged file', function () {
    expect(runSetupSupervisor($this->sandbox)->successful())->toBeTrue();
    $first = File::get("{$this->sandbox['sup']}/rconfig-core-horizon.ini");

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(File::get("{$this->sandbox['sup']}/rconfig-core-horizon.ini"))->toBe($first)
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(['rconfig-core-horizon.ini']);
});

test('backs up a hand edited rendered file before replacing it', function () {
    $target = "{$this->sandbox['sup']}/rconfig-core-horizon.ini";
    File::put($target, "[program:rconfig-core-horizon]\nuser=root\nnumprocs=4\n");

    $result = runSetupSupervisor($this->sandbox);

    $backups = File::glob("{$target}.rconfig-bak-*");

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($backups)->toHaveCount(1)
        ->and(File::get($backups[0]))->toContain('numprocs=4')
        ->and(File::get($target))->toContain("\nuser=nobody\n");
});

test('fails before writing anything when the web user does not exist', function () {
    $result = runSetupSupervisor($this->sandbox, 'rconfig_no_such_user');

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain("web user 'rconfig_no_such_user' does not exist")
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('fails before writing anything when the web user is root', function () {
    $result = runSetupSupervisor($this->sandbox, 'root');

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('must never run as root')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('fails before writing anything when artisan cannot report the web user', function () {
    $result = runSetupSupervisor($this->sandbox, env: ['FAKE_PHP_EXIT' => '1']);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('could not detect the web user')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('fails before writing anything when a placeholder is left after rendering', function () {
    $stub = "{$this->sandbox['app']}/horizon_supervisor.ini";
    File::put($stub, str_replace(SETUP_SUPERVISOR_STUB_PLACEHOLDER, 'user=RCONFIG_WEBUSER', File::get($stub)));

    $result = runSetupSupervisor($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('still contains a placeholder')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('fails before writing anything when the stub has no user line', function () {
    $stub = "{$this->sandbox['app']}/horizon_supervisor.ini";
    File::put($stub, str_replace(SETUP_SUPERVISOR_STUB_PLACEHOLDER . "\n", '', File::get($stub)));

    $result = runSetupSupervisor($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('has no user=nobody line')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('a failed run leaves an existing old symlink in place', function () {
    $target = "{$this->sandbox['sup']}/horizon_supervisor.ini";
    symlink("{$this->sandbox['app']}/horizon_supervisor.ini", $target);

    $result = runSetupSupervisor($this->sandbox, 'rconfig_no_such_user');

    expect($result->exitCode())->not->toBe(0)
        ->and(is_link($target))->toBeTrue()
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(['horizon_supervisor.ini']);
});

test('fails when the Supervisor config folder does not exist', function () {
    $result = runSetupSupervisor($this->sandbox, env: ['RCONFIG_SUPERVISOR_DIR' => "{$this->sandbox['root']}/missing"]);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('Supervisor config folder')
        ->and($result->errorOutput())->toContain('not found');
});

test('detects each supported OS from os-release and picks its config extension and service', function (string $osRelease, string $id, string $family) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupSupervisor($this->sandbox, env: ['RCONFIG_SUPERVISOR_EXT' => '']);

    [$extension, $service] = $family === 'rhel' ? ['ini', 'supervisord'] : ['conf', 'supervisor'];

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->toContain("OS: {$id} ({$family} family), Supervisor service {$service}")
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe(["rconfig-core-horizon.{$extension}"]);
})->with('supported os-release files');

test('refuses an unsupported OS before writing anything', function (string $osRelease) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupSupervisor($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain('unsupported OS')
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
})->with('unsupported os-release files');

test('fails without an os-release file rather than guessing the OS', function () {
    File::delete($this->sandbox['os-release']);

    $result = runSetupSupervisor($this->sandbox);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain("cannot read {$this->sandbox['os-release']}")
        ->and(supervisorDirEntries($this->sandbox['sup']))->toBe([]);
});

test('neither setup script detects the OS from redhat-release, debian_version or lsb-release', function (string $script) {
    $code = collect(file(base_path($script), FILE_IGNORE_NEW_LINES))
        ->reject(fn (string $line): bool => str_starts_with(ltrim($line), '#'))
        ->implode("\n");

    expect($code)->not->toMatch('#/etc/(redhat-release|debian_version|lsb-release|system-release)#');
})->with(['setup_supervisor.sh', 'setup_cron.sh']);

test('reads single quoted os-release values', function () {
    File::put($this->sandbox['os-release'], "ID='ubuntu'\nID_LIKE='debian'\n");

    $result = runSetupSupervisor($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($result->output())->toContain('OS: ubuntu (debian family), Supervisor service supervisor');
});

test('a missing Supervisor folder says how to install Supervisor on the detected OS', function (string $osRelease, string $hint) {
    File::put($this->sandbox['os-release'], $osRelease);

    $result = runSetupSupervisor($this->sandbox, env: ['RCONFIG_SUPERVISOR_DIR' => "{$this->sandbox['root']}/missing"]);

    expect($result->exitCode())->not->toBe(0)
        ->and($result->errorOutput())->toContain($hint);
})->with([
    'Rocky' => ["ID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n", 'dnf install supervisor'],
    'Ubuntu' => ["ID=ubuntu\nID_LIKE=debian\n", 'apt install supervisor'],
    'Amazon Linux 2023' => ["ID=\"amzn\"\nID_LIKE=\"fedora\"\n", 'pip3 install supervisor'],
]);

test('rendered files are owned by root with mode 0644 when run as root', function () {
    if (! function_exists('posix_geteuid') || posix_geteuid() !== 0) {
        $this->markTestSkipped('Real ownership can only be checked when the suite runs as root.');
    }

    $result = runSetupSupervisor($this->sandbox);
    $target = "{$this->sandbox['sup']}/rconfig-core-horizon.ini";

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(fileowner($target))->toBe(0)
        ->and(filegroup($target))->toBe(0)
        ->and(fileperms($target) & 0777)->toBe(0644);
});

test('no Supervisor stub in the repo runs as root', function () {
    $stubs = File::glob(base_path('*.ini'));

    expect($stubs)->not->toBeEmpty();

    foreach ($stubs as $stub) {
        $contents = File::get($stub);

        $this->assertDoesNotMatchRegularExpression('/^\s*user\s*=\s*root\s*$/mi', $contents, basename($stub) . ' sets user=root.');
        $this->assertStringContainsString(SETUP_SUPERVISOR_STUB_PLACEHOLDER, $contents, basename($stub) . ' must use the RCONFIG_WEB_USER placeholder.');
    }
});
