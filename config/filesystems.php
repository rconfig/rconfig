<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
     */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
     */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'rconfig' => [ // DO NOT CHANGE
            'driver' => 'local',
            'root' => storage_path('app/rconfig'),
        ],
        'rconfig_exports' => [ // DO NOT CHANGE
            'driver' => 'local',
            'root' => storage_path('app/rconfig/exports'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],
        'ftp' => [
            'driver' => 'ftp',
            'root' => '',
            'host' => env('BACKUP_FTP_HOST', 'ftp.example.com'),
            'username' => env('BACKUP_FTP_USERNAME', 'your-username'),
            'password' => env('BACKUP_FTP_PASSWORD', 'your-password'),
            // Optional FTP Settings...
            'port' => (int) env('BACKUP_FTP_PORT', 21),
            'root' => env('BACKUP_FTP_ROOT', ''),
            'passive' => (bool) env('BACKUP_FTP_PASSIVE', true),
            'ssl' => (bool) env('BACKUP_FTP_SSL', true),
            'timeout' => (int) env('BACKUP_FTP_TIMEOUT', 30),
        ],
        // 'sftp' => [
        //     'driver' => 'sftp',
        //     'host' => env('BACKUP_SFTP_HOST', 'ftp.example.com'),
        //     'username' => env('BACKUP_SFTP_USERNAME', 'your-username'),
        //     'password' => env('BACKUP_SFTP_PASSWORD', 'your-password'),
        //     // Optional FTP Settings...
        //     'port' => env('BACKUP_SFTP_PORT', 21),
        //     'root' => env('BACKUP_SFTP_ROOT', ''),
        //     'timeout' => env('BACKUP_SFTP_TIMEOUT', 30),
        //     // Settings for SSH key based authentication...
        //     'privateKey' => env('BACKUP_SFTP_PRIVATEKEY', '/path/to/privateKey'),
        //     'encryptionPassword' => env('BACKUP_SFTP_ENCRYPTIONPASSWORD', 'encryption-password'),
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
     */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
