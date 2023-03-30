<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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

function download_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/downloads/';
}
function report_path()
{
    return rconfig_appdir_storage_path() . '/app/rconfig/reports/';
}

function custom_chown($path)
{
    File::exists('/etc/redhat-release') ? chown($path, 'apache') : chown($path, 'www-data');
}

function formatSize($bytes)
{
    $types = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);

    return round($bytes, 2) . ' ' . $types[$i];
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
    /* and get disk space total (in bytes)  */
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
    if (false == function_exists('shell_exec') || false == is_readable('/etc/os-release')) {
        return null;
    }

    $os = shell_exec('cat /etc/os-release');
    $listIds = preg_match_all('/.*=/', $os, $matchListIds);
    $listIds = $matchListIds[0];

    $listVal = preg_match_all('/=.*/', $os, $matchListVal);
    $listVal = $matchListVal[0];

    array_walk($listIds, function (&$v, $k) {
        $v = strtolower(str_replace('=', '', $v));
    });

    array_walk($listVal, function (&$v, $k) {
        $v = preg_replace('/=|"/', '', $v);
    });

    return array_combine($listIds, $listVal);
}

function activityLogIt($class, $function, $log_name, $description, $event_type, $device_name = null, $device_id = null, $connection_category = null, $connection_ids = null)
{
    // test covered
    activity($log_name)
        ->tap(function (Spatie\Activitylog\Contracts\Activity $activity) use ($class, $function, $event_type, $device_name, $device_id, $connection_category, $connection_ids) {
            $activity->event_type = $event_type;
            $activity->device_name = $device_name;
            $activity->device_id = $device_id;
            $activity->connection_category = $connection_category;
            $activity->connection_ids = serialize($connection_ids);
            $activity->class = $class;
            $activity->function = $function;
        })
        ->log($description);

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
