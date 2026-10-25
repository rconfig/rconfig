<?php

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Drives update.sh against a throwaway app folder. PATH is only the sandbox
 * bin folder: a recording fake for every binary that could change the host
 * (git, composer, php, supervisorctl, systemctl, runuser, chown, id, sleep),
 * plus links to an allow-list of harmless tools (sed, awk, find and so on).
 * A binary the script was not expected to call is "command not found", the
 * run fails and afterEach fails the test, so nothing real is ever reached.
 *
 * Every fake appends "[<user>] <binary> <args>" to calls.log, where <user> is
 * root unless the call came through the fake runuser. The fake Supervisor
 * keeps each program's state as a file, the fake proc folder holds the
 * processes the script inspects, and the two setup scripts in the sandbox
 * app folder are fakes too, so the real ones never run.
 */
const UPDATE_SAFE_TOOLS = [
    'bash', 'env', 'sed', 'awk', 'grep', 'cat', 'tr', 'cut', 'head', 'tail', 'dirname', 'basename',
    'readlink', 'mkdir', 'mktemp', 'rm', 'cp', 'mv', 'date', 'tee', 'find', 'getent', 'stat', 'flock',
    'touch', 'ls',
];

beforeEach(function () {
    $root = sys_get_temp_dir() . '/rconfig-update-' . bin2hex(random_bytes(6));

    $this->sandbox = [
        'root' => $root,
        'app' => "{$root}/app",
        'bin' => "{$root}/bin",
        'proc' => "{$root}/proc",
        'logs' => "{$root}/logs",
        'sup' => "{$root}/supervisor-state",
        'calls' => "{$root}/calls.log",
        'os-release' => "{$root}/os-release",
    ];

    foreach (['app', 'bin', 'proc', 'logs', 'sup'] as $dir) {
        File::makeDirectory($this->sandbox[$dir], 0755, true);
    }

    buildUpdateApp($this->sandbox);
    putOsRelease($this->sandbox, "NAME=\"Rocky Linux\"\nID=\"rocky\"\nID_LIKE=\"rhel centos fedora\"\n");
    buildUpdateBin($this->sandbox);

    // Horizon is running under its new name before the upgrade.
    setProgramState($this->sandbox, 'rconfig-core-horizon', 'RUNNING');
});

afterEach(function () {
    File::deleteDirectory($this->sandbox['root']);
});

/**
 * A minimal Core app folder with fake setup scripts.
 *
 * @param  array<string, string>  $sandbox
 */
function buildUpdateApp(array $sandbox): void
{
    $app = $sandbox['app'];

    File::copy(base_path('update.sh'), "{$app}/update.sh");
    chmod("{$app}/update.sh", 0755);

    File::put("{$app}/artisan", "<?php // fake\n");
    File::put("{$app}/composer.json", "{\n    \"name\": \"rconfig/rconfig\",\n    \"description\": \"rConfig V8 Core 8.2.19\"\n}\n");
    File::put("{$app}/.env", "APP_ENV=production\n");
    File::put("{$app}/horizon_supervisor.ini", "[program:rconfig-core-horizon]\n");
    File::makeDirectory("{$app}/.git");

    foreach (['storage/logs', 'storage/framework', 'storage/app/rconfig/data', 'bootstrap/cache'] as $dir) {
        File::makeDirectory("{$app}/{$dir}", 0755, true);
    }
    File::put("{$app}/storage/logs/laravel.log", "log\n");
    File::put("{$app}/storage/app/rconfig/data/showrun.txt", "config\n");
    File::put("{$app}/bootstrap/cache/packages.php", "<?php return [];\n");

    // Both setup scripts run artisan as root in real life, which is why the
    // final ownership fix has to come after them.
    foreach (['setup_supervisor.sh', 'setup_cron.sh'] as $script) {
        File::put("{$app}/{$script}", <<<SH
            #!/bin/sh
            . '{$sandbox['bin']}/.fake-lib'
            record {$script} "defer=\${RCONFIG_SUPERVISOR_DEFER_START:-}"
            php '{$app}/artisan' rconfig:web-user >/dev/null
            SH);
        chmod("{$app}/{$script}", 0755);
    }
}

/**
 * @param  array<string, string>  $sandbox
 */
function buildUpdateBin(array $sandbox): void
{
    $bin = $sandbox['bin'];
    $calls = $sandbox['calls'];
    $sup = $sandbox['sup'];

    foreach (UPDATE_SAFE_TOOLS as $tool) {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($tool)));
        expect($path)->not->toBe('', "{$tool} is needed by the update.sh tests");
        symlink($path, "{$bin}/{$tool}");
    }

    // Shared by every fake: log the call, then fail it if FAKE_FAIL matches.
    File::put("{$bin}/.fake-lib", <<<SH
        record() {
            name="\$1"; shift
            printf '[%s] %s\\n' "\${FAKE_AS:-root}" "\$name \$*" >> '{$calls}'
            if [ -n "\${FAKE_FAIL:-}" ]; then
                case "\$name \$* " in *"\$FAKE_FAIL "*) echo "fake \$name: failing on purpose" >&2; exit 1 ;; esac
            fi
        }
        SH);

    $fakes = [
        'id' => <<<'SH'
            [ "$1" = "-u" ] && echo "${FAKE_UID:-0}"
            SH,
        'sleep' => ':',
        'chown' => <<<SH
            record chown "\$@"
            if [ -n "\${FAKE_CHOWN_FAIL_ONCE:-}" ] && [ ! -e '{$sandbox['root']}/chown-failed' ]; then
                touch '{$sandbox['root']}/chown-failed'
                exit 1
            fi
            SH,
        'runuser' => <<<'SH'
            record runuser "$1" "$2"
            [ "$1" = "-u" ] && [ "$3" = "--" ] || { echo "fake runuser: expected -u USER --" >&2; exit 2; }
            FAKE_AS="$2"; export FAKE_AS
            shift 3
            exec "$@"
            SH,
        'git' => <<<'SH'
            record git "$@"
            if [ "$3" = "pull" ] && [ -n "${FAKE_GIT_NEW_SCRIPT:-}" ] && [ ! "$FAKE_GIT_NEW_SCRIPT" -ef "$2/update.sh" ]; then
                cp "$FAKE_GIT_NEW_SCRIPT" "$2/update.sh"
            fi
            SH,
        'composer' => 'record composer "$@"',
        'php' => <<<'SH'
            record php "$@"
            app="$(dirname "$1")"
            case "$2" in
                rconfig:web-user) printf '%s\n' "${FAKE_WEB_USER:-nobody}" ;;
                down) touch "$app/storage/framework/down" ;;
                up) rm -f "$app/storage/framework/down" ;;
                rconfig:check-worker-user)
                    if [ -n "${FAKE_NO_CHECK_WORKER_USER:-}" ]; then
                        echo 'ERROR  Command "rconfig:check-worker-user" is not defined.' >&2
                        exit 1
                    fi
                    if [ -n "${FAKE_WORKER_CHECK_FAILS:-}" ]; then
                        printf 'FAIL Horizon: Horizon runs as root (PID 4321). It must run as nobody only.\n'
                        printf 'OK   Scheduler cron file: /etc/cron.d/rconfig-core is root:root 0644 and runs the scheduler as nobody.\n'
                        printf 'FAIL Supervisor config: /etc/supervisord.d/rconfig-core-horizon.ini sets user=root, not nobody.\n'
                        printf 'Overall: Still running as root\nFix: Run sudo ./update.sh from %s\n' "$app"
                        exit 1
                    fi
                    printf 'OK   Horizon: Horizon runs as nobody (PID 4321).\nOverall: All good\n'
                    ;;
            esac
            SH,
        'supervisorctl' => <<<SH
            record supervisorctl "\$@"
            case "\$1" in
                status)
                    for f in '{$sup}'/*; do
                        [ -f "\$f" ] && printf '%-30s %s\\n' "\$(basename "\$f")" "\$(cat "\$f")"
                    done
                    exit 3
                    ;;
                pid) cat '{$sup}'/"\$2.pid" 2>/dev/null || echo 0 ;;
                stop) echo STOPPED > '{$sup}'/"\$2" ;;
                start) echo RUNNING > '{$sup}'/"\$2" ;;
                update)
                    # A program Supervisor did not know yet is added and,
                    # with autostart=true, started.
                    [ -f '{$sup}/rconfig-core-horizon' ] || echo RUNNING > '{$sup}/rconfig-core-horizon'
                    ;;
            esac
            SH,
        'systemctl' => <<<'SH'
            record systemctl "$@"
            case "$1" in
                is-active) [ -z "${FAKE_SUPERVISOR_STOPPED:-}" ] ;;
                cat) [ -z "${FAKE_NO_APACHE:-}" ] ;;
                list-units)
                    for unit in ${FAKE_FPM_UNITS:-}; do
                        printf '%s loaded active running PHP FastCGI\n' "$unit"
                    done
                    ;;
            esac
            SH,
    ];

    foreach ($fakes as $name => $body) {
        $needsLib = str_contains($body, 'record');
        File::put("{$bin}/{$name}", "#!/bin/sh\n" . ($needsLib ? ". '{$bin}/.fake-lib'\n" : '') . $body . "\n");
        chmod("{$bin}/{$name}", 0755);
    }
}

/**
 * @param  array<string, string>  $sandbox
 */
function putOsRelease(array $sandbox, string $contents): void
{
    File::put($sandbox['os-release'], $contents);
}

/**
 * @param  array<string, string>  $sandbox
 */
function setProgramState(array $sandbox, string $program, ?string $state, ?int $pid = null): void
{
    if ($state === null) {
        @unlink("{$sandbox['sup']}/{$program}");

        return;
    }

    File::put("{$sandbox['sup']}/{$program}", "{$state}\n");

    if ($pid !== null) {
        File::put("{$sandbox['sup']}/{$program}.pid", "{$pid}\n");
    }
}

/**
 * A process in the fake proc folder.
 *
 * @param  array<string, string>  $sandbox
 * @param  list<string>  $arguments
 */
function putProcess(array $sandbox, int $pid, array $arguments): void
{
    File::makeDirectory("{$sandbox['proc']}/{$pid}");
    File::put("{$sandbox['proc']}/{$pid}/cmdline", implode("\0", $arguments) . "\0");
}

/**
 * @param  array<string, string>  $sandbox
 * @param  list<string>  $arguments
 * @param  array<string, string>  $env
 */
function runUpdate(array $sandbox, array $arguments = ['--no-pull'], array $env = [], ?string $script = null): ProcessResult
{
    return Process::timeout(60)->env(array_merge([
        'PATH' => $sandbox['bin'],
        'RCONFIG_UPDATE_KEEP_PATH' => '1',
        'RCONFIG_OS_RELEASE' => $sandbox['os-release'],
        'RCONFIG_UPDATE_LOG_DIR' => $sandbox['logs'],
        'RCONFIG_UPDATE_LOCK' => "{$sandbox['root']}/update.lock",
        'RCONFIG_PROC_DIR' => $sandbox['proc'],
        'RCONFIG_HORIZON_START_WAIT' => '3',
        'IS_DOCKER' => '',
        'FAKE_WEB_USER' => 'nobody',
    ], $env))->run(array_merge([$script ?? "{$sandbox['app']}/update.sh"], $arguments));
}

/**
 * @param  array<string, string>  $sandbox
 * @return list<string>
 */
function updateCalls(array $sandbox): array
{
    return file_exists($sandbox['calls']) ? file($sandbox['calls'], FILE_IGNORE_NEW_LINES) : [];
}

/**
 * Whether a call contains $needle followed by a space or the end of the
 * line, so "artisan optimize" does not match "artisan optimize:clear".
 */
function callMatches(string $call, string $needle): bool
{
    return preg_match('/' . preg_quote($needle, '/') . '(?=\\s|$)/', $call) === 1;
}

/**
 * Index of the first matching call, or null.
 *
 * @param  list<string>  $calls
 */
function firstCall(array $calls, string $needle): ?int
{
    foreach ($calls as $index => $call) {
        if (callMatches($call, $needle)) {
            return $index;
        }
    }

    return null;
}

/**
 * Index of the last matching call, or null.
 *
 * @param  list<string>  $calls
 */
function lastCall(array $calls, string $needle): ?int
{
    $found = null;
    foreach ($calls as $index => $call) {
        if (callMatches($call, $needle)) {
            $found = $index;
        }
    }

    return $found;
}

/**
 * Everything the run printed. Once the log is open, stderr goes through tee
 * to stdout, so both streams are read together.
 */
function updateOutput(ProcessResult $result): string
{
    return $result->output() . $result->errorOutput();
}

/**
 * The user the code owner steps run as: the owner of the sandbox .git.
 *
 * @param  array<string, string>  $sandbox
 */
function sandboxCodeOwner(array $sandbox): string
{
    return posix_getpwuid(fileowner("{$sandbox['app']}/.git"))['name'];
}

function expectNoUnplannedBinary(ProcessResult $result): void
{
    expect($result->output() . $result->errorOutput())->not->toContain('command not found');
}

// --- Happy path and order -----------------------------------------------

test('a full run does every step in the right order and ends with the app up and Horizon running', function () {
    $result = runUpdate($this->sandbox);
    $calls = updateCalls($this->sandbox);
    $app = $this->sandbox['app'];

    expectNoUnplannedBinary($result);
    expect($result->successful())->toBeTrue($result->output() . $result->errorOutput());

    $order = [
        "[root] php {$app}/artisan down",
        'supervisorctl stop rconfig-core-horizon',
        'composer install --no-dev --no-interaction --no-scripts',
        "[root] php {$app}/artisan rconfig:web-user",
        "[nobody] php {$app}/artisan optimize:clear",
        "[nobody] php {$app}/artisan package:discover",
        "[nobody] php {$app}/artisan down",
        "[nobody] php {$app}/artisan migrate --force",
        "[nobody] php {$app}/artisan rconfig:sync-tasks",
        "[nobody] php {$app}/artisan rconfig:redis-secure --no-restart",
        'setup_supervisor.sh defer=1',
        'setup_cron.sh',
        "[nobody] php {$app}/artisan optimize",
        'supervisorctl update',
        'systemctl restart httpd',
        "[nobody] php {$app}/artisan up",
        "[nobody] php {$app}/artisan rconfig:set-config-permissions",
        "[nobody] php {$app}/artisan rconfig:check-worker-user",
    ];

    $positions = array_map(fn (string $needle): ?int => firstCall($calls, $needle), $order);

    expect($positions)->not->toContain(null)
        ->and($positions)->toBe(collect($positions)->sort()->values()->all())
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING')
        ->and(file_exists("{$app}/storage/framework/down"))->toBeFalse()
        ->and(updateOutput($result))->toContain('done. rConfig V8 Core is upgraded.');
});

test('every artisan call after composer runs as the web user, except the two setup scripts and the web user lookup', function () {
    runUpdate($this->sandbox);
    $calls = updateCalls($this->sandbox);
    $composer = firstCall($calls, 'composer install');

    $rootArtisanAfterComposer = array_values(array_filter(
        array_slice($calls, $composer + 1),
        fn (string $call): bool => str_starts_with($call, '[root] php ')
    ));

    expect($rootArtisanAfterComposer)->toHaveCount(3)
        ->and($rootArtisanAfterComposer)->each->toEndWith('artisan rconfig:web-user');
});

test('ownership is fixed after every root artisan step and before Horizon starts', function () {
    runUpdate($this->sandbox);
    $calls = updateCalls($this->sandbox);

    $lastRootArtisan = lastCall($calls, '[root] php');
    $lastChown = lastCall($calls, '[root] chown');
    $firstStart = min(array_filter([
        firstCall($calls, 'supervisorctl update'),
        firstCall($calls, 'supervisorctl start rconfig-core-horizon'),
        firstCall($calls, 'systemctl start'),
    ], fn (?int $index): bool => $index !== null));

    expect($lastChown)->not->toBeNull()
        ->and($lastChown)->toBeGreaterThan($lastRootArtisan)
        ->and($firstStart)->toBeGreaterThan($lastChown)
        ->and($calls[$lastChown])->toContain('-h nobody:');
});

test('Horizon is never started before ownership is fixed, and setup_supervisor.sh is told not to start it', function () {
    runUpdate($this->sandbox);
    $calls = updateCalls($this->sandbox);

    $lastChown = lastCall($calls, '[root] chown');
    $starts = array_keys(array_filter($calls, fn (string $call): bool => str_contains($call, 'supervisorctl update')
        || str_contains($call, 'supervisorctl start')
        || str_contains($call, 'systemctl start')));

    expect(firstCall($calls, 'setup_supervisor.sh defer=1'))->not->toBeNull()
        ->and($starts)->not->toBeEmpty()
        ->and(min($starts))->toBeGreaterThan($lastChown);
});

test('git and composer run as the owner of the app folder', function () {
    $owner = sandboxCodeOwner($this->sandbox);

    runUpdate($this->sandbox, [], ['FAKE_GIT_NEW_SCRIPT' => "{$this->sandbox['app']}/update.sh"]);
    $calls = updateCalls($this->sandbox);

    expect($calls[firstCall($calls, 'git -C')])->toStartWith("[{$owner}] ")
        ->and($calls[firstCall($calls, 'composer install')])->toStartWith("[{$owner}] ");
});

test('stale compiled manifests are removed after composer so artisan can boot without dev packages', function () {
    $result = runUpdate($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(file_exists("{$this->sandbox['app']}/bootstrap/cache/packages.php"))->toBeFalse();
});

// --- Ownership fix only touches what needs it -------------------------------

test('the ownership fix passes every file not owned by the web user to chown -h', function () {
    $result = runUpdate($this->sandbox);
    $chowned = implode("\n", array_filter(updateCalls($this->sandbox), fn (string $call): bool => str_contains($call, '] chown -h')));
    $app = $this->sandbox['app'];

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and($chowned)->toContain("{$app}/storage/logs/laravel.log")
        ->and($chowned)->toContain("{$app}/storage/app/rconfig/data/showrun.txt")
        ->and($chowned)->toContain("{$app}/bootstrap/cache");
});

test('the ownership fix leaves files already owned by the web user alone', function () {
    $app = $this->sandbox['app'];

    // The suite runs as root here and as an ordinary user in CI. As root the
    // tree is handed to nobody; otherwise the current user is the web user.
    if (posix_geteuid() === 0) {
        $webUser = 'nobody';
        $nobody = posix_getpwnam('nobody');
        foreach (['storage', 'bootstrap'] as $dir) {
            $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$app}/{$dir}", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
            foreach ([new SplFileInfo("{$app}/{$dir}"), ...$items] as $item) {
                chown($item->getPathname(), $nobody['uid']);
                chgrp($item->getPathname(), $nobody['gid']);
            }
        }
        // One file still owned by root, as an older release would leave it.
        File::put("{$app}/storage/logs/root-owned.log", "x\n");
        chown("{$app}/storage/logs/root-owned.log", 0);
    } else {
        $webUser = posix_getpwuid(posix_geteuid())['name'];
    }

    $result = runUpdate($this->sandbox, env: ['FAKE_WEB_USER' => $webUser]);
    $chowns = array_values(array_filter(updateCalls($this->sandbox), fn (string $call): bool => str_contains($call, '] chown -h')));

    expect($result->successful())->toBeTrue($result->errorOutput());

    if (posix_geteuid() === 0) {
        // The down file is written by the root maintenance step, so it is
        // the only other file that needs handing over.
        $paths = collect($chowns)
            ->flatMap(fn (string $call): array => array_slice(explode(' ', $call), 4))
            ->unique()->sort()->values()->all();

        expect($paths)->toBe(["{$app}/storage/framework/down", "{$app}/storage/logs/root-owned.log"]);
    } else {
        expect($chowns)->toBe([]);
    }
});

// --- git pull and the re-exec -------------------------------------------------

test('after git pull the rest of the run uses the new copy of the script', function () {
    $new = "{$this->sandbox['root']}/update-new.sh";
    $marker = "printf '[new-script] started %s\\n' \"\$*\" >> '{$this->sandbox['calls']}'";
    File::put($new, preg_replace('/^set -Eeuo pipefail$/m', "set -Eeuo pipefail\n{$marker}", File::get(base_path('update.sh')), 1));

    $result = runUpdate($this->sandbox, [], ['FAKE_GIT_NEW_SCRIPT' => $new]);
    $calls = updateCalls($this->sandbox);

    $pull = firstCall($calls, 'git -C');
    $started = firstCall($calls, '[new-script] started --no-pull --resumed');

    expect($result->successful())->toBeTrue($result->output() . $result->errorOutput())
        ->and($calls[$pull])->toContain("git -C {$this->sandbox['app']} pull --ff-only")
        ->and($started)->toBeGreaterThan($pull)
        ->and(firstCall($calls, 'composer install'))->toBeGreaterThan($started)
        ->and(lastCall($calls, 'rconfig:check-worker-user'))->toBeGreaterThan($started)
        ->and(count(array_filter($calls, fn (string $call): bool => str_contains($call, 'composer install'))))->toBe(1);
});

test('the resumed run keeps the options it was given', function () {
    $result = runUpdate($this->sandbox, ['--skip-redis-secure'], ['FAKE_GIT_NEW_SCRIPT' => "{$this->sandbox['app']}/update.sh"]);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'rconfig:redis-secure'))->toBeNull()
        ->and(updateOutput($result))->toContain('skipped (--skip-redis-secure)');
});

test('--no-pull skips git entirely', function () {
    $result = runUpdate($this->sandbox, ['--no-pull']);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'git'))->toBeNull()
        ->and(firstCall(updateCalls($this->sandbox), 'composer install'))->not->toBeNull();
});

// --- Failures -------------------------------------------------------------------

test('a failure at a step stops the run, says which step, and runs nothing after it', function (string $fail, string $step, string $nextCall, string $message) {
    $result = runUpdate($this->sandbox, ['--no-pull'], ['FAKE_FAIL' => $fail]);
    $calls = updateCalls($this->sandbox);

    expectNoUnplannedBinary($result);
    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain("the step '{$step}' failed")
        ->and(updateOutput($result))->toContain($message)
        ->and(firstCall($calls, $nextCall))->toBeNull()
        ->and(updateOutput($result))->not->toContain('done. rConfig V8 Core is upgraded.');
})->with([
    'composer' => ['composer install', 'composer install', 'rconfig:web-user', 'left in maintenance mode'],
    'ownership' => ['chown -h', 'fix ownership', 'artisan optimize:clear', 'left in maintenance mode'],
    'migrate' => ['artisan migrate', 'migrate', 'setup_supervisor.sh', 'left in maintenance mode'],
    'setup_supervisor.sh' => ['setup_supervisor.sh', 'Supervisor config', 'setup_cron.sh', 'left in maintenance mode'],
    'setup_cron.sh' => ['setup_cron.sh', 'scheduler cron file', 'artisan optimize', 'left in maintenance mode'],
    'rebuild caches' => ['artisan optimize', 'rebuild caches', 'supervisorctl update', 'left in maintenance mode'],
    'start Horizon' => ['supervisorctl update', 'start Horizon', 'systemctl restart', 'left in maintenance mode'],
    'web server' => ['systemctl restart httpd', 'restart the web server', 'artisan up', 'left in maintenance mode'],
]);

test('a chown that fails once, for example on a file that vanished, gets a second pass', function () {
    $result = runUpdate($this->sandbox, env: ['FAKE_CHOWN_FAIL_ONCE' => '1']);

    expect($result->successful())->toBeTrue(updateOutput($result))
        ->and(updateOutput($result))->toContain('trying once more');
});

test('a failure after code changes leaves the app in maintenance mode with Horizon stopped', function () {
    $result = runUpdate($this->sandbox, ['--no-pull'], ['FAKE_FAIL' => 'artisan migrate']);

    expect(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeTrue()
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('STOPPED')
        ->and(updateOutput($result))->toContain('sudo ./update.sh --no-pull');
});

test('a failed git pull rolls back: Horizon restarts and the app comes back up', function () {
    $result = runUpdate($this->sandbox, [], ['FAKE_FAIL' => 'git -C']);
    $calls = updateCalls($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain("the step 'git pull' failed")
        ->and(updateOutput($result))->toContain('Nothing was changed')
        ->and(firstCall($calls, 'composer'))->toBeNull()
        ->and(firstCall($calls, 'artisan up'))->toBeGreaterThan(firstCall($calls, 'git -C'))
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING')
        ->and(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeFalse();
});

test('a failure stopping Horizon rolls back and never pulls', function () {
    $result = runUpdate($this->sandbox, [], ['FAKE_FAIL' => 'supervisorctl stop']);
    $calls = updateCalls($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain("the step 'stop Horizon' failed")
        ->and(firstCall($calls, 'git'))->toBeNull()
        ->and(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeFalse();
});

test('a failed worker check after the app is up says the app is up, lists the failed checks and gives the fix', function () {
    $result = runUpdate($this->sandbox, ['--no-pull'], ['FAKE_WORKER_CHECK_FAILS' => '1']);
    $output = updateOutput($result);

    expect($result->exitCode())->toBe(1)
        ->and($output)->toContain("the step 'check the worker user' failed (exit 1)")
        ->and($output)->toContain('The upgrade itself finished. rConfig is up and running.')
        ->and($output)->toContain("Failed checks:\nupdate:   FAIL Horizon: Horizon runs as root (PID 4321).")
        ->and($output)->toContain('update:   FAIL Supervisor config:')
        ->and($output)->not->toContain('update:   OK')
        ->and($output)->toContain("Fix: correct each failed check, then run: cd {$this->sandbox['app']} && sudo ./update.sh --no-pull")
        ->and($output)->not->toContain('left in maintenance mode and Horizon is stopped')
        ->and($output)->not->toContain('done. rConfig V8 Core is upgraded.')
        ->and(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeFalse()
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING');
});

test('a failed worker check on a box that was already in maintenance mode says it was left that way', function () {
    touch("{$this->sandbox['app']}/storage/framework/down");

    $result = runUpdate($this->sandbox, ['--no-pull'], ['FAKE_WORKER_CHECK_FAILS' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('left in maintenance mode as it was before this run')
        ->and(updateOutput($result))->not->toContain('rConfig is up and running');
});

test('Horizon that never reaches RUNNING fails the run', function () {
    // The fake start leaves the program in BACKOFF instead of RUNNING.
    File::put("{$this->sandbox['bin']}/supervisorctl", str_replace(
        'start) echo RUNNING >',
        'start) echo BACKOFF >',
        File::get("{$this->sandbox['bin']}/supervisorctl")
    ));

    $result = runUpdate($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('did not reach RUNNING within 3 seconds')
        ->and(firstCall(updateCalls($this->sandbox), 'systemctl restart'))->toBeNull();
});

// --- Horizon program names ----------------------------------------------------------

test('the old horizon program is stopped when it runs this install', function () {
    setProgramState($this->sandbox, 'rconfig-core-horizon', null);
    setProgramState($this->sandbox, 'horizon', 'RUNNING', 4242);
    putProcess($this->sandbox, 4242, ['php', "{$this->sandbox['app']}/artisan", 'horizon']);

    $result = runUpdate($this->sandbox);
    $calls = updateCalls($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall($calls, 'supervisorctl stop horizon'))->toBeLessThan(firstCall($calls, 'composer install'))
        ->and(firstCall($calls, 'supervisorctl stop rconfig-core-horizon'))->toBeNull()
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING');
});

test('a horizon program that runs another app, such as rConfig PRO, is left alone', function () {
    setProgramState($this->sandbox, 'horizon', 'RUNNING', 5151);
    putProcess($this->sandbox, 5151, ['php', "{$this->sandbox['app']}8/artisan", 'horizon']);

    $result = runUpdate($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'supervisorctl stop horizon'))->toBeNull()
        ->and(firstCall(updateCalls($this->sandbox), 'supervisorctl stop rconfig-core-horizon'))->not->toBeNull()
        ->and(updateOutput($result))->toContain('leaving the horizon program alone')
        ->and(trim(File::get("{$this->sandbox['sup']}/horizon")))->toBe('RUNNING');
});

test('a stopped or unknown rconfig-core-horizon is not stopped again, and is started at the end', function (?string $state) {
    setProgramState($this->sandbox, 'rconfig-core-horizon', $state);

    $result = runUpdate($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'supervisorctl stop'))->toBeNull()
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING');
})->with(['STOPPED' => 'STOPPED', 'not known to Supervisor' => null]);

test('supervisord is started first when it is not running', function () {
    $result = runUpdate($this->sandbox, env: ['FAKE_SUPERVISOR_STOPPED' => '1']);
    $calls = updateCalls($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall($calls, 'systemctl start supervisord'))->toBeLessThan(firstCall($calls, 'supervisorctl update'));
});

// --- Redis ------------------------------------------------------------------------------

test('redis-secure is skipped with a warning when another app\'s Horizon is running', function () {
    putProcess($this->sandbox, 7001, ['php', '/var/www/html/rconfig8/artisan', 'horizon']);

    $result = runUpdate($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'rconfig:redis-secure'))->toBeNull()
        ->and(updateOutput($result))->toContain("another app's Horizon is running");
});

test('a Horizon worker subcommand from another app does not count as its Horizon', function () {
    putProcess($this->sandbox, 7002, ['php', '/var/www/html/rconfig8/artisan', 'horizon:work', 'redis']);

    runUpdate($this->sandbox);

    expect(firstCall(updateCalls($this->sandbox), 'rconfig:redis-secure --no-restart'))->not->toBeNull();
});

test('a failed redis-secure is a warning and the upgrade carries on', function () {
    $result = runUpdate($this->sandbox, env: ['FAKE_FAIL' => 'artisan rconfig:redis-secure']);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(updateOutput($result))->toContain('rconfig:redis-secure failed')
        ->and(firstCall(updateCalls($this->sandbox), 'setup_supervisor.sh'))->not->toBeNull();
});

// --- check-worker-user ------------------------------------------------------------------

test('check-worker-user always runs, as the web user, and its output reaches the log', function () {
    $result = runUpdate($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), "[nobody] php {$this->sandbox['app']}/artisan rconfig:check-worker-user"))->not->toBeNull()
        ->and(updateOutput($result))->toContain('Overall: All good')
        ->and(updateOutput($result))->toContain('done. rConfig V8 Core is upgraded.');
});

test('a missing check-worker-user command fails the run instead of being skipped', function () {
    $result = runUpdate($this->sandbox, ['--no-pull'], ['FAKE_NO_CHECK_WORKER_USER' => '1']);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('Command "rconfig:check-worker-user" is not defined.')
        ->and(updateOutput($result))->toContain("the step 'check the worker user' failed")
        ->and(updateOutput($result))->toContain('none listed, see the output above.')
        ->and(updateOutput($result))->not->toContain('done. rConfig V8 Core is upgraded.');
});

// --- Re-runs and maintenance mode -------------------------------------------------------

test('running it twice gives the same calls and the same end state', function () {
    $first = runUpdate($this->sandbox);
    $firstCalls = updateCalls($this->sandbox);
    unlink($this->sandbox['calls']);

    $second = runUpdate($this->sandbox);

    expect($first->successful())->toBeTrue($first->errorOutput())
        ->and($second->successful())->toBeTrue($second->errorOutput())
        ->and(updateCalls($this->sandbox))->toBe($firstCalls)
        ->and(trim(File::get("{$this->sandbox['sup']}/rconfig-core-horizon")))->toBe('RUNNING')
        ->and(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeFalse();
});

test('an app that was already in maintenance mode is left in maintenance mode', function () {
    File::put("{$this->sandbox['app']}/storage/framework/down", '{}');

    $result = runUpdate($this->sandbox, [], ['FAKE_GIT_NEW_SCRIPT' => "{$this->sandbox['app']}/update.sh"]);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall(updateCalls($this->sandbox), 'artisan up'))->toBeNull()
        ->and(file_exists("{$this->sandbox['app']}/storage/framework/down"))->toBeTrue()
        ->and(updateOutput($result))->toContain('was already in maintenance mode');
});

test('a second run cannot start while one holds the lock', function () {
    $lock = fopen("{$this->sandbox['root']}/update.lock", 'c');
    flock($lock, LOCK_EX);

    $result = runUpdate($this->sandbox);

    flock($lock, LOCK_UN);
    fclose($lock);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('another update.sh run is in progress')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('the run is logged to a file', function () {
    runUpdate($this->sandbox);

    $logs = File::glob("{$this->sandbox['logs']}/rconfig-core-update-*.log");

    expect($logs)->toHaveCount(1)
        ->and(File::get($logs[0]))->toContain('==> migrate');
});

// --- Web server ------------------------------------------------------------------------------

test('Ubuntu restarts apache2 and any active php-fpm unit', function () {
    putOsRelease($this->sandbox, "NAME=\"Ubuntu\"\nID=ubuntu\nID_LIKE=debian\n");

    $result = runUpdate($this->sandbox, env: ['FAKE_FPM_UNITS' => 'php8.4-fpm.service']);
    $calls = updateCalls($this->sandbox);

    expect($result->successful())->toBeTrue($result->errorOutput())
        ->and(firstCall($calls, 'systemctl restart apache2'))->not->toBeNull()
        ->and(firstCall($calls, 'systemctl restart php8.4-fpm.service'))->not->toBeNull()
        ->and(firstCall($calls, 'systemctl restart httpd'))->toBeNull();
});

// --- Refusals ----------------------------------------------------------------------------------

test('it refuses to run as a user other than root', function () {
    $result = runUpdate($this->sandbox, env: ['FAKE_UID' => '1000']);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('must be run as root')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('it refuses to run from a folder that is not a Core install', function () {
    $elsewhere = "{$this->sandbox['root']}/elsewhere";
    File::makeDirectory($elsewhere);
    File::copy(base_path('update.sh'), "{$elsewhere}/update.sh");
    chmod("{$elsewhere}/update.sh", 0755);

    $result = runUpdate($this->sandbox, script: "{$elsewhere}/update.sh");

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('is not an rConfig V8 Core install')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('it refuses to upgrade an rConfig PRO folder', function () {
    File::put("{$this->sandbox['app']}/composer.json", "{\n    \"name\": \"rconfig/rconfig\",\n    \"description\": \"rConfig V8 8.4.0\"\n}\n");

    $result = runUpdate($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('This script only upgrades Core')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('it refuses to run on an unsupported OS', function () {
    putOsRelease($this->sandbox, "NAME=\"Arch Linux\"\nID=arch\n");

    $result = runUpdate($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('unsupported OS (ID=arch')
        ->and(updateOutput($result))->toContain('Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later.')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('it refuses Amazon Linux before changing anything and says how to stay on the current version', function () {
    putOsRelease($this->sandbox, "NAME=\"Amazon Linux\"\nID=\"amzn\"\nID_LIKE=\"fedora\"\nVERSION_ID=\"2023\"\n");

    $result = runUpdate($this->sandbox);
    $output = updateOutput($result);
    $app = $this->sandbox['app'];
    $owner = sandboxCodeOwner($this->sandbox);

    expect($result->exitCode())->toBe(1)
        ->and($output)->toContain('update: ERROR: Amazon Linux is no longer supported from 8.2.19. Stay on your current version and contact support. Supported: Rocky, AlmaLinux, RHEL and CentOS 8 or 9, and Ubuntu 22.04 or later.')
        ->and($output)->toContain("cd {$app} && sudo -u {$owner} git reset --keep ORIG_HEAD")
        ->and($output)->toContain("cd {$app} && sudo -u {$owner} composer install --no-dev --optimize-autoloader")
        ->and($output)->not->toContain('--no-pull')
        ->and($output)->not->toContain('Fix the error above')
        ->and(updateCalls($this->sandbox))->toBe([])
        ->and(file_exists("{$app}/storage/framework/down"))->toBeFalse();
});

test('it refuses to run inside Docker', function () {
    $result = runUpdate($this->sandbox, env: ['IS_DOCKER' => 'true']);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain('Docker')
        ->and(updateCalls($this->sandbox))->toBe([]);
});

test('it refuses an unknown option before doing anything', function () {
    $result = runUpdate($this->sandbox, ['--force']);

    expect($result->exitCode())->toBe(1)
        ->and(updateOutput($result))->toContain("unknown option '--force'")
        ->and(updateCalls($this->sandbox))->toBe([]);
});

// --- Static guards -------------------------------------------------------------------------------

test('update.sh never calls a binary by absolute path, so the fakes on PATH always win', function () {
    $lines = array_slice(file(base_path('update.sh'), FILE_IGNORE_NEW_LINES), 1);
    $code = array_filter($lines, fn (string $line): bool => ! str_starts_with(ltrim($line), '#'));

    expect(implode("\n", $code))->not->toMatch('#(^|[\s;&|(`])/(usr/)?s?bin/#m');
});
