<?php

return [

    'app_dir_path' => env('APP_DIR_PATH', '/var/www/html/rconfig'),

    /*
    |--------------------------------------------------------------------------
    | Backup Disk Configuration
    |--------------------------------------------------------------------------
    |
     */
    'backup_destination' => env('BACKUP_DESTINATION', 'rconfig'),
    'is_demo' => env('IS_DEMO', false),
    'mask_device_credentials' => env('MASK_DEVICE_CREDENTIALS', false),

    /*
    |--------------------------------------------------------------------------
    | Downloaded Configuration Permissions
    |--------------------------------------------------------------------------
    |
    | Device configurations routinely contain secrets (VPN pre shared keys,
    | RADIUS secrets, SNMP community strings), so they are written with no
    | "other" permission bits, inside directories that unprivileged local
    | accounts cannot traverse. Both are required: a 0440 file inside a 0755
    | directory is still readable by every account on the host.
    |
    | An explicit chmod overrides the process umask, so these values, not the
    | host umask, decide the final mode. Only loosen them if the web server and
    | the queue worker run as users that share no common group, and prefer
    | fixing the group membership instead.
    |
    | Values are octal strings, for example '0440'. Changing them affects newly
    | written configs only, so run rconfig:fix-config-permissions afterwards to
    | apply the change to configs already on disk.
    |
     */
    'config_file_mode' => octdec((string) env('RCONFIG_CONFIG_FILE_MODE', '0440')),
    'config_dir_mode' => octdec((string) env('RCONFIG_CONFIG_DIR_MODE', '0750')),
];
