<?php

namespace App\Services\System;

use App\Enums\WorkerCheckStatus;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\Cache;

/**
 * Checks that this install runs nothing as root: Horizon, the scheduler cron
 * file, root's cron table and the Supervisor config. rconfig:check-worker-user
 * and the WorkerPrivilegeCheck dashboard check both read report(), so they
 * never disagree. Add a new check here, never in either caller.
 *
 * Only things that belong to this app folder count, so rConfig PRO's Horizon,
 * cron line or Supervisor file on a shared box is never mistaken for Core's.
 */
class WorkerUserCheckService
{
    public const CRON_FILE = '/etc/cron.d/rconfig-core';

    public const SUPERVISOR_PROGRAM = 'rconfig-core-horizon';

    /**
     * Set by WorkerPrivilegeCheck when it runs as root. Health checks run from
     * schedule:run, so that means the scheduler itself runs as root.
     */
    public const ROOT_SCHEDULER_CACHE_KEY = 'rconfig:scheduler-ran-as-root';

    /**
     * Longer than the five minute health check interval, so one missed run
     * does not clear the warning.
     */
    public const ROOT_SCHEDULER_MARKER_MINUTES = 15;

    /**
     * Old Supervisor file names, shared with rConfig PRO. They only count when
     * they provably run this app folder.
     */
    private const LEGACY_SUPERVISOR_NAMES = ['horizon_supervisor.ini', 'horizon_supervisor.conf'];

    public function __construct(private readonly HostFilesystem $host) {}

    /**
     * The OS user the web server, and so Horizon, is expected to run as.
     *
     * @return array{0: string, 1: string}
     */
    public function expectedUser(): array
    {
        return rconfig_web_user();
    }

    /**
     * Running Horizon master processes for this install. Matches
     * "<this app>/artisan horizon" only, never horizon:work, horizon:snapshot
     * or another app folder's artisan.
     *
     * @return list<array{pid: int, uid: int, user: string, command: string}>
     */
    public function runningHorizonProcesses(): array
    {
        $artisanPaths = array_unique(array_filter([
            base_path('artisan'),
            realpath(base_path('artisan')) ?: null,
        ]));

        $matches = [];

        foreach ($this->host->processes() as $process) {
            $arguments = $process['arguments'];

            foreach ($arguments as $index => $argument) {
                if (in_array($argument, $artisanPaths, true) && ($arguments[$index + 1] ?? null) === 'horizon') {
                    $matches[] = [
                        'pid' => $process['pid'],
                        'uid' => $process['uid'],
                        'user' => $this->host->userName($process['uid']) ?? (string) $process['uid'],
                        'command' => implode(' ', $arguments),
                    ];

                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * Runs every check and works out the state of the box.
     *
     * state: all_good, still_root, mixed, not_set_up, misconfigured or docker.
     * status: the worst check status (skipped in Docker).
     *
     * @return array{
     *     state: string,
     *     summary: string,
     *     status: WorkerCheckStatus,
     *     web_user: string,
     *     checked_as_root: bool,
     *     checks: list<array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>}>,
     *     fix: string|null
     * }
     */
    public function report(): array
    {
        [$webUser] = $this->expectedUser();
        $isRoot = $this->host->isRoot();

        if ($this->host->isDocker()) {
            return [
                'state' => 'docker',
                'summary' => 'Docker: the container runs Horizon and the scheduler itself, so the bare-metal checks were skipped',
                'status' => WorkerCheckStatus::Skipped,
                'web_user' => $webUser,
                'checked_as_root' => $isRoot,
                'checks' => [],
                'fix' => null,
            ];
        }

        $checks = [
            $this->horizonCheck($webUser),
            $this->cronFileCheck($webUser),
            $this->rootCrontabCheck($isRoot),
            $this->supervisorConfigCheck($webUser),
        ];

        [$state, $summary] = $this->overallState($checks);

        $statuses = array_column($checks, 'status');
        $status = match (true) {
            in_array(WorkerCheckStatus::Fail, $statuses, true) => WorkerCheckStatus::Fail,
            in_array(WorkerCheckStatus::Warn, $statuses, true) => WorkerCheckStatus::Warn,
            default => WorkerCheckStatus::Ok,
        };

        return [
            'state' => $state,
            'summary' => $summary,
            'status' => $status,
            'web_user' => $webUser,
            'checked_as_root' => $isRoot,
            'checks' => array_map(function (array $check): array {
                unset($check['evidence']);

                return $check;
            }, $checks),
            'fix' => $status === WorkerCheckStatus::Fail ? 'Run sudo ./update.sh from ' . base_path() : null,
        ];
    }

    /**
     * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>, evidence: list<string>}
     */
    private function horizonCheck(string $webUser): array
    {
        $processes = $this->runningHorizonProcesses();
        $details = ['processes' => array_map(fn (array $process): array => [
            'pid' => $process['pid'],
            'user' => $process['user'],
        ], $processes)];

        if ($processes === []) {
            return $this->check('horizon', 'Horizon', WorkerCheckStatus::Warn,
                'Horizon is not running, so its user could not be checked. If supervisorctl shows '
                . self::SUPERVISOR_PROGRAM . ' as RUNNING, /proc may be mounted with hidepid, which hides other users\' processes from this check.',
                $details, ['missing']);
        }

        $pidsByUser = [];

        foreach ($processes as $process) {
            $pidsByUser[$process['user']][] = $process['pid'];
        }

        $described = implode(' and ', array_map(
            fn (string $user, array $pids): string => $user . ' (PID' . (count($pids) > 1 ? 's ' : ' ') . implode(', ', $pids) . ')',
            array_keys($pidsByUser),
            $pidsByUser,
        ));

        $evidence = [];

        foreach ($processes as $process) {
            $evidence[] = match (true) {
                $process['uid'] === 0 => 'root',
                $process['user'] === $webUser => 'web',
                default => 'other',
            };
        }

        if (array_keys($pidsByUser) === [$webUser]) {
            return $this->check('horizon', 'Horizon', WorkerCheckStatus::Ok, "Horizon runs as {$described}.", $details, ['web']);
        }

        return $this->check('horizon', 'Horizon', WorkerCheckStatus::Fail,
            "Horizon runs as {$described}. It must run as {$webUser} only.", $details, array_values(array_unique($evidence)));
    }

    /**
     * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>, evidence: list<string>}
     */
    private function cronFileCheck(string $webUser): array
    {
        $path = self::CRON_FILE;
        $label = 'Scheduler cron file';
        $details = ['path' => $path];

        [$problem, $evidence] = $this->rootOwnedFileProblem($path);

        if ($problem !== null) {
            $message = $evidence === ['missing'] ? "{$path} is missing, so the scheduler is not set up to run as {$webUser}." : $problem;

            return $this->check('scheduler_cron_file', $label, WorkerCheckStatus::Fail, $message, $details, $evidence);
        }

        $problems = $this->ownershipProblems($path);
        $contents = $this->host->readFile($path);

        if ($contents === null) {
            $problems[] = "{$path} could not be read.";

            return $this->check('scheduler_cron_file', $label, WorkerCheckStatus::Fail, implode(' ', $problems), $details, []);
        }

        $users = array_map(fn (string $line): ?string => $this->cronLineUser($line), $this->schedulerLines($contents));
        $details['users'] = $users;
        $evidence = array_unique($users) === [$webUser] ? ['web'] : [];

        if ($users === []) {
            $problems[] = "{$path} has no line that runs this install's scheduler.";
        } elseif (array_unique($users) !== [$webUser]) {
            $others = implode(', ', array_unique(array_map(fn (?string $user): string => $user ?? 'an unknown user', $users)));
            $problems[] = "{$path} runs the scheduler as {$others}, not {$webUser}.";
            $evidence[] = in_array('root', $users, true) ? 'root' : 'other';
        }

        if ($problems !== []) {
            return $this->check('scheduler_cron_file', $label, WorkerCheckStatus::Fail, implode(' ', $problems), $details, $evidence);
        }

        return $this->check('scheduler_cron_file', $label, WorkerCheckStatus::Ok,
            "{$path} is root:root 0644 and runs the scheduler as {$webUser}.", $details, ['web']);
    }

    /**
     * Root's cron table can only be read as root. A scheduler run as root that
     * WorkerPrivilegeCheck saw recently is reported either way, since it may
     * come from somewhere else, such as /etc/crontab.
     *
     * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>, evidence: list<string>}
     */
    private function rootCrontabCheck(bool $isRoot): array
    {
        $label = 'Root crontab';
        $sawRootScheduler = Cache::has(self::ROOT_SCHEDULER_CACHE_KEY);
        $recentRun = 'The scheduler ran as root in the last ' . self::ROOT_SCHEDULER_MARKER_MINUTES . ' minutes';

        if (! $isRoot) {
            if ($sawRootScheduler) {
                return $this->check('root_crontab', $label, WorkerCheckStatus::Warn,
                    "{$recentRun}. Root's cron table was not read, because this check is not running as root. If you have just run update.sh, this clears on its own.",
                    ['read' => false], ['root']);
            }

            return $this->check('root_crontab', $label, WorkerCheckStatus::Skipped,
                "Not running as root, so root's cron table was not read. Run this check with sudo to include it.",
                ['read' => false], []);
        }

        $crontab = $this->host->readCrontab('root');

        if ($crontab['exitCode'] !== 0 && stripos($crontab['errorOutput'], 'no crontab for') === false) {
            return $this->check('root_crontab', $label, WorkerCheckStatus::Warn,
                "Root's cron table could not be read: " . trim($crontab['errorOutput']),
                ['read' => false], []);
        }

        $lines = $crontab['exitCode'] === 0 ? $this->schedulerLines($crontab['output']) : [];
        $details = ['read' => true, 'lines' => $lines];

        if ($lines !== []) {
            return $this->check('root_crontab', $label, WorkerCheckStatus::Fail,
                "Root's cron table still runs this install's scheduler as root: " . implode(' | ', $lines),
                $details, ['root']);
        }

        if ($sawRootScheduler) {
            return $this->check('root_crontab', $label, WorkerCheckStatus::Warn,
                "{$recentRun}, but root's cron table has no line for it. Check /etc/crontab and any other cron file that runs schedule:run for this install.",
                $details, ['root']);
        }

        return $this->check('root_crontab', $label, WorkerCheckStatus::Ok,
            "Root's cron table does not run this install's scheduler.", $details, []);
    }

    /**
     * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>, evidence: list<string>}
     */
    private function supervisorConfigCheck(string $webUser): array
    {
        $label = 'Supervisor config';
        $location = $this->supervisorLocation();

        if ($location === null) {
            return $this->check('supervisor_config', $label, WorkerCheckStatus::Warn,
                'Could not tell the OS family from /etc/os-release, so the Supervisor config was not checked.', [], []);
        }

        [$directory, $extension] = $location;
        $path = "{$directory}/" . self::SUPERVISOR_PROGRAM . ".{$extension}";
        $details = ['path' => $path];
        $problems = [];
        $evidence = [];

        [$problem, $fileEvidence] = $this->rootOwnedFileProblem($path);

        if ($problem !== null) {
            $problems[] = $fileEvidence === ['missing'] ? "{$path} is missing, so Supervisor does not run Horizon for this install." : $problem;
            $evidence = $fileEvidence;
        } else {
            $problems = $this->ownershipProblems($path);
            $userProblem = $this->supervisorUserProblem($path, $webUser);

            if ($userProblem !== null) {
                $problems[] = $userProblem[0];
                $evidence[] = $userProblem[1];
            } else {
                $evidence[] = 'web';
            }
        }

        $legacy = $this->legacySupervisorFiles($directory);
        $details['legacy'] = $legacy;

        if ($legacy !== []) {
            $problems[] = 'Old Supervisor config ' . implode(', ', $legacy) . ' still runs this install, so Supervisor may start a second Horizon from it, as root.';
            $evidence[] = 'root';
        }

        if ($problems === []) {
            return $this->check('supervisor_config', $label, WorkerCheckStatus::Ok,
                "{$path} is root:root 0644 and runs Horizon as {$webUser}.", $details, ['web']);
        }

        return $this->check('supervisor_config', $label, WorkerCheckStatus::Fail, implode(' ', $problems), $details, array_values(array_unique($evidence)));
    }

    /**
     * The problem with a file that must be a root owned regular file, before
     * its owner and mode are looked at: missing, a symlink, or not a file.
     *
     * @return array{0: string|null, 1: list<string>}
     */
    private function rootOwnedFileProblem(string $path): array
    {
        $stat = $this->host->linkStat($path);

        return match ($stat['type'] ?? null) {
            null => ["{$path} is missing.", ['missing']],
            'link' => ["{$path} is a symlink. It must be a regular file owned by root, so the web user cannot change what root loads.", []],
            'file' => [null, []],
            default => ["{$path} is not a regular file.", []],
        };
    }

    /**
     * @return list<string>
     */
    private function ownershipProblems(string $path): array
    {
        $stat = $this->host->linkStat($path);
        $problems = [];

        if ($stat === null) {
            return [];
        }

        if ($stat['uid'] !== 0 || $stat['gid'] !== 0) {
            $owner = $this->host->userName($stat['uid']) ?? "uid {$stat['uid']}";
            $problems[] = "{$path} is owned by {$owner} (uid {$stat['uid']}, gid {$stat['gid']}), not root:root.";
        }

        if ($stat['mode'] !== 0644) {
            $problems[] = sprintf('%s has mode %04o, not 0644.', $path, $stat['mode']);
        }

        return $problems;
    }

    /**
     * What is wrong with the user= line of this install's program, with the
     * evidence it gives. Supervisor runs a program with no user= as the user
     * supervisord runs as, which is root.
     *
     * @return array{0: string, 1: string}|null
     */
    private function supervisorUserProblem(string $path, string $webUser): ?array
    {
        $contents = $this->host->readFile($path);

        if ($contents === null) {
            return ["{$path} could not be read.", 'other'];
        }

        if (! preg_match_all('/^\s*user\s*=\s*(\S+)/m', $contents, $matches)) {
            return ["{$path} has no user= line, so Supervisor runs Horizon as root.", 'root'];
        }

        $users = array_values(array_unique($matches[1]));

        if ($users === [$webUser]) {
            return null;
        }

        return ["{$path} sets user=" . implode(', user=', $users) . ", not {$webUser}.", in_array('root', $users, true) ? 'root' : 'other'];
    }

    /**
     * Old Supervisor files in the folder that provably run this install: a
     * symlink into this app folder, or a regular file whose command= runs
     * from it. Matches setup_supervisor.sh, so PRO's file is never counted.
     *
     * @return list<string>
     */
    private function legacySupervisorFiles(string $directory): array
    {
        $found = [];

        foreach (self::LEGACY_SUPERVISOR_NAMES as $name) {
            $path = "{$directory}/{$name}";
            $stat = $this->host->linkStat($path);

            if ($stat === null) {
                continue;
            }

            if ($stat['type'] === 'link') {
                $resolved = $this->host->resolvePath($path);

                if ($resolved !== null && $this->startsWithAppDirectory($resolved)) {
                    $found[] = $path;
                }

                continue;
            }

            if ($stat['type'] === 'file' && $this->runsFromAppDirectory($this->host->readFile($path) ?? '')) {
                $found[] = $path;
            }
        }

        return $found;
    }

    /**
     * Whether a Supervisor config has a command= with a word under this app
     * folder.
     */
    private function runsFromAppDirectory(string $contents): bool
    {
        preg_match_all('/^\s*command\s*=(.*)$/m', $contents, $matches);

        foreach ($matches[1] as $command) {
            foreach (preg_split('/\s+/', trim($command)) ?: [] as $word) {
                if ($this->startsWithAppDirectory($word)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function startsWithAppDirectory(string $path): bool
    {
        foreach ($this->appDirectories() as $directory) {
            if (str_starts_with($path, "{$directory}/")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lines of a cron table that run this install's scheduler, matched the
     * same way setup_cron.sh matches them: not a comment, a schedule:run word,
     * and a word that is this app folder or a path under it.
     *
     * @return list<string>
     */
    private function schedulerLines(string $contents): array
    {
        $lines = [];

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            if (preg_match('/^\s*#/', $line)) {
                continue;
            }

            $words = array_map(fn (string $word): string => $this->trimShellWord($word), preg_split('/\s+/', trim($line)) ?: []);

            $hasSchedule = in_array('schedule:run', $words, true);
            $hasAppDirectory = array_filter($words, fn (string $word): bool => $this->isAppDirectoryWord($word)) !== [];

            if ($hasSchedule && $hasAppDirectory) {
                $lines[] = trim($line);
            }
        }

        return $lines;
    }

    /**
     * The user field of a system cron line: the sixth field, or the second
     * after an @reboot style shortcut.
     */
    private function cronLineUser(string $line): ?string
    {
        $fields = preg_split('/\s+/', trim($line)) ?: [];

        return str_starts_with($fields[0] ?? '', '@') ? ($fields[1] ?? null) : ($fields[5] ?? null);
    }

    private function trimShellWord(string $word): string
    {
        return rtrim(ltrim($word, "\"'("), "\"';&|)");
    }

    private function isAppDirectoryWord(string $word): bool
    {
        foreach ($this->appDirectories() as $directory) {
            if ($word === $directory || str_starts_with($word, "{$directory}/")) {
                return true;
            }
        }

        return false;
    }

    /**
     * This app folder, by the path Laravel knows and by its resolved path, in
     * case it was reached through a symlink.
     *
     * @return list<string>
     */
    private function appDirectories(): array
    {
        return array_values(array_unique(array_filter([
            base_path(),
            realpath(base_path()) ?: null,
        ])));
    }

    /**
     * The Supervisor config folder and file extension for this OS, from ID and
     * ID_LIKE in os-release, the same way setup_supervisor.sh decides.
     *
     * @return array{0: string, 1: string}|null
     */
    private function supervisorLocation(): ?array
    {
        $osRelease = $this->host->readOsRelease() ?? '';
        $words = [];

        foreach (['ID', 'ID_LIKE'] as $key) {
            if (preg_match_all('/^' . $key . '=(.*)$/m', $osRelease, $matches)) {
                $value = strtolower(trim((string) end($matches[1]), "\"' \t"));
                $words = array_merge($words, preg_split('/\s+/', $value) ?: []);
            }
        }

        foreach ($words as $word) {
            if (in_array($word, ['rhel', 'centos', 'rocky', 'almalinux', 'ol'], true)) {
                return ['/etc/supervisord.d', 'ini'];
            }

            if (in_array($word, ['ubuntu', 'debian'], true)) {
                return ['/etc/supervisor/conf.d', 'conf'];
            }
        }

        return null;
    }

    /**
     * The one-line state of the box, from the evidence each check found.
     *
     * @param  list<array{status: WorkerCheckStatus, key: string, evidence: list<string>}>  $checks
     * @return array{0: string, 1: string}
     */
    private function overallState(array $checks): array
    {
        if (! in_array(WorkerCheckStatus::Fail, array_column($checks, 'status'), true)) {
            return ['all_good', 'All good'];
        }

        $evidence = array_merge(...array_column($checks, 'evidence'));
        $byKey = array_column($checks, 'evidence', 'key');

        $hasRoot = in_array('root', $evidence, true);
        $hasWeb = in_array('web', $evidence, true);

        return match (true) {
            $hasRoot && $hasWeb => ['mixed', 'Mixed: some parts run as the web user and some as root'],
            $hasRoot => ['still_root', 'Still running as root'],
            $byKey['horizon'] === ['missing'] && $byKey['scheduler_cron_file'] === ['missing'] && $byKey['supervisor_config'] === ['missing'] => ['not_set_up', 'Not set up'],
            default => ['misconfigured', 'Misconfigured'],
        };
    }

    /**
     * @param  array<string, mixed>  $details
     * @param  list<string>  $evidence  root, web, missing or other: what the check says about who runs this install
     * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>, evidence: list<string>}
     */
    private function check(string $key, string $label, WorkerCheckStatus $status, string $message, array $details, array $evidence): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'message' => $message,
            'details' => $details,
            'evidence' => $evidence,
        ];
    }
}
