<?php

use App\Models\User;
use App\Notifications\DBNotification;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Contracts\Activity;

/**
 * Return active class if current path begins with this path.
 *
 * @param  string  $path
 * @return string
 */
// function setActive($path)
// {
//     return Request::is($path . '*') ? ' active' : '';
// }

/**
 * Return Formatted Text From Path Stub
 *
 * @param  string  $path
 * @return string
 */
function setPageName($path)
{
    $title = str_replace('-', ' ', $path);
    $title = ucwords($title);

    return $title;
}
/**
 * Return Formatted Text From Path Stub
 *
 * @param  string  $path
 * @return string
 */
function setPagePath($path)
{
    $title = str_replace('-', ' ', $path);

    // $title = ucwords($title);
    return $title;
}

function isTrue($value): bool
{
    return $value === 'true' || $value === true || $value === 1 || $value === '1';
}

if (! function_exists('getComposerVersion')) {
    function getComposerVersion()
    {
        $composerFile = base_path('composer.json');
        $composerData = json_decode(file_get_contents($composerFile), true);

        return $composerData['version'] ?? '8.0.0';  // Default version if not found
    }
}

// used in routes files to load routes from a file
function loadRoutesFrom($path)
{
    return require rconfig_appdir_path() . '/routes' . $path;
}

function rconfig_appdir_path()
{
    return Config::get('rConfig.app_dir_path');
}

function rconfig_appdir_storage_path()
{
    return rconfig_appdir_path() . '/storage';
}

function rconfig_app_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/';
}

function templates_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/templates/';
}

function config_data_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/data/';
}

function export_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/exports/';
}

function download_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/downloads/';
}
function report_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/reports/';
}

function tmp_dir()
{
    $tempDir = rconfig_appdir_storage_path() . '/app/rconfig/tempdir/';

    // Cleaned copies of device configs land here, so it gets the same
    // treatment as the config data dir rather than being world readable.
    rconfig_make_directory($tempDir, (int) Config::get('rConfig.config_dir_mode'));

    return $tempDir;
}

/**
 * The OS user and group the web server, and therefore Horizon and the
 * scheduler, run as on this host. Matches rConfig PRO (rconfig8) exactly.
 *
 * RHEL family distributions (Amazon Linux, CentOS, Red Hat, Rocky, Oracle,
 * AlmaLinux), identified from /etc/os-release, get apache. Everything else,
 * including a missing or unreadable /etc/os-release, falls back to www-data.
 * It never throws, and nginx is not detected.
 *
 * This does not check that the returned user exists on the host. Callers that
 * write config (Supervisor, cron) or chown must validate it with
 * posix_getpwnam() first and fail loudly if it is missing.
 *
 * @return array{0: string, 1: string} [user, group]
 */
function rconfig_web_user(): array
{
    $user = 'www-data';

    $osRelease = app(HostFilesystem::class)->readOsRelease();

    if ($osRelease !== null) {
        if (
            stripos($osRelease, 'Amazon Linux') !== false ||
            stripos($osRelease, 'CentOS') !== false ||
            stripos($osRelease, 'Red Hat') !== false ||
            stripos($osRelease, 'Rocky') !== false ||
            stripos($osRelease, 'Oracle') !== false ||
            stripos($osRelease, 'AlmaLinux') !== false
        ) {
            $user = 'apache';
        }
    }

    return [$user, $user];
}

/**
 * Chown and chgrp a path (optionally its whole tree) to the web user.
 *
 * Never throws, matching PRO. A process that is not root cannot hand a file to
 * another user, so failure there is expected and stays silent. Unlike PRO, a
 * failure while running as root is logged as a warning, since that points at a
 * real problem such as a missing web user.
 */
function custom_chown($path, bool $recursive = false): void
{
    $filesystem = app(HostFilesystem::class);
    [$user, $group] = rconfig_web_user();

    $chownOne = function (string $target) use ($filesystem, $user, $group): void {
        $owned = $filesystem->chown($target, $user);
        $grouped = $filesystem->chgrp($target, $group);

        if ((! $owned || ! $grouped) && $filesystem->isRoot()) {
            Log::warning("custom_chown: could not change ownership of {$target} to {$user}:{$group}");
        }
    };

    $chownOne($path);

    if ($recursive && is_dir($path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $chownOne($item->getPathname());
        }
    }
}

/**
 * Create a directory path, chmodding and chowning every level this call
 * creates, not just the leaf. A plain mkdir($path, $mode, true) silently
 * creates missing ancestors too, and chowning only the leaf afterwards left
 * those ancestors owned by whichever user ran the process (RCO-1317).
 *
 * - Already exists: a no-op, so repeat calls are safe.
 * - Existing levels, including ones with the wrong owner or mode, are never
 *   touched. Only levels this call created are changed.
 * - Not running as root: directories are still created and chmodded, the
 *   chown fails silently (see custom_chown()).
 * - Lost race: if another worker creates a level first, that level is treated
 *   as existing and left alone. A level that cannot be created and does not
 *   exist afterwards throws.
 *
 * Same signature as PRO. Two deliberate differences from PRO, to be aligned
 * there under RCO-1790: each created level is chmodded to $mode so the umask
 * cannot loosen it, and levels are created one at a time so a lost race is
 * tolerated and only this call's levels are chowned. Always pass $mode
 * explicitly at call sites; the default exists only to match PRO.
 *
 * @throws RuntimeException
 */
function rconfig_make_directory(string $path, int $mode = 0755): void
{
    $filesystem = app(HostFilesystem::class);
    $path = rtrim($path, '/');

    if ($path === '' || $filesystem->isDirectory($path)) {
        return;
    }

    $missingLevels = [];
    $level = $path;

    while ($level !== '' && $level !== '/' && $level !== '.' && ! $filesystem->isDirectory($level)) {
        array_unshift($missingLevels, $level);
        $level = dirname($level);
    }

    foreach ($missingLevels as $missingLevel) {
        if (! $filesystem->makeDirectory($missingLevel, $mode)) {
            if ($filesystem->isDirectory($missingLevel)) {
                continue;
            }

            throw new RuntimeException("Unable to create directory: {$missingLevel}");
        }

        $filesystem->chmod($missingLevel, $mode);
        custom_chown($missingLevel);
    }
}

function isDocker(): bool
{
    return is_file('/.dockerenv');
}

function formatSize($bytes)
{
    $types = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);

    return round($bytes, 2) . ' ' . $types[$i];
}

function ping($host)
{
    exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($host)), $res, $rval);

    return $rval === 0;
}

function dir_size($directory)
{
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveIteratorIterator::SELF_FIRST)) as $file) {
        $size += $file->getSize();
    }

    return $size;
}

function diskInfo()
{
    /* get disk space free (in bytes) */
    $df = disk_free_space('/');
    /* and get disk space total (in bytes) */
    $dt = disk_total_space('/');
    /* now we calculate the disk space used (in bytes) */
    $du = $dt - $df;
    /* percentage of disk used - this will be used to also set the width % of the progress bar */
    $dp = sprintf('%.2f', ($du / $dt) * 100);

    /* and we formate the size from bytes to MB, GB, etc. */

    $df = formatSize($df);
    $du = formatSize($du);
    $dt = formatSize($dt);

    $disk = [
        'Disk Free Space' => $df,
        'Disk Used Space' => $du,
        'Disk Total Space' => $dt,
        'Disk Percent Used' => $dp . '%',
    ];

    return $disk;
}

function getOSInformation()
{
    if (function_exists('shell_exec') == false || is_readable('/etc/os-release') == false) {
        return null;
    }

    $osRelease = null;
    $osReleaseFile = '/etc/os-release';
    if (file_exists($osReleaseFile)) {
        $osRelease = parse_ini_file($osReleaseFile);
    } else {
        $osReleaseFile = '/usr/lib/os-release';
        if (file_exists($osReleaseFile)) {
            $osRelease = parse_ini_file($osReleaseFile);
        }
    }

    return $osRelease;
}
function activityLogIt($class, $function, $log_name, $description, $event_type, $device_name = null, $device_id = null, $connection_category = null, $connection_ids = null)
{
    // test covered
    activity($log_name)
        ->tap(function (Activity $activity) use ($class, $function, $event_type, $device_name, $device_id, $connection_category, $connection_ids) {
            $activity->event_type = $event_type;
            $activity->device_name = $device_name;
            $activity->device_id = $device_id;
            $activity->connection_category = $connection_category;
            $activity->connection_ids = serialize($connection_ids);
            $activity->class = $class;
            $activity->function = $function;
        })
        ->log($description);

    Notification::send(User::all(), new DBNotification($log_name, $description, $event_type, $log_name, 'pficon-info'));

    // log_name = severity level
    //     critical - pficon-error-circle-o
    //     error - pficon-error-circle-o
    //     warn - pficon-warning-triangle-o
    //     info - pficon-info
    //     default - pficon-info

    // description
    // {Class/Function: MSG}

    // event_type
    // scheduler
    // downloader
    // connection
    // config
    // backup

    // device_name
    //  string

    // device_id
    // int

    // connection_ids
    //  int or array

    // connection_category
    // task
    // tag
    // category
    // device
}
