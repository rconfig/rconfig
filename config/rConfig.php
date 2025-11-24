<?php

return [

    'app_dir_path' => env('APP_DIR_PATH', '/var/www/html/rconfig6-core'),

    /*
    |--------------------------------------------------------------------------
    | Backup Disk Configuration
    |--------------------------------------------------------------------------
    |
     */
    'backup_destination' => env('BACKUP_DESTINATION', 'rconfig'),
    'is_demo' => env('IS_DEMO', false),
    'mask_device_credentials' => env('MASK_DEVICE_CREDENTIALS', false),
];
