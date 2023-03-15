<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enlightn Analyzer Classes
    |--------------------------------------------------------------------------
    |
    | The following array lists the "analyzer" classes that will be registered
    | with Enlightn. These analyzers run an analysis on the application via
    | various methods such as static analysis. Feel free to customize it.
    |
     */
    'analyzers' => ['*'],

    // If you wish to skip running some analyzers, list the classes in the array below.
    'exclude_analyzers' => [
        \Enlightn\Enlightn\Analyzers\Performance\EnvCallAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\RouteCachingAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\CustomErrorPageAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\PHPIniAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\FilePermissionsAnalyzer::class,
    ],

    // If you wish to skip running some analyzers in CI mode, list the classes below.
    'ci_mode_exclude_analyzers' => [],

    /*
    |--------------------------------------------------------------------------
    | Enlightn Analyzer Paths
    |--------------------------------------------------------------------------
    |
    | The following array lists the "analyzer" paths that will be searched
    | recursively to find analyzer classes. This option will only be used
    | if the analyzers option above is set to the asterisk wildcard. The
    | key is the base namespace to resolve the class name.
    |
     */
    'analyzer_paths' => [
        'Enlightn\\Enlightn\\Analyzers' => base_path('vendor/enlightn/enlightn/src/Analyzers'),
        'Enlightn\\EnlightnPro\\Analyzers' => base_path('vendor/enlightn/enlightnpro/src/Analyzers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enlightn Base Path
    |--------------------------------------------------------------------------
    |
    | The following array lists the directories that will be scanned for
    | application specific code. By default, we are scanning your app
    | folder, migrations folder and the seeders folder.
    |
     */
    'base_path' => [
        app_path(),
        database_path('migrations'),
        database_path('seeders'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Specific Analyzers
    |--------------------------------------------------------------------------
    |
    | There are some analyzers that are meant to be run for specific environments.
    | The options below specify whether we should skip environment specific
    | analyzers if the environment does not match.
    |
     */
    'skip_env_specific' => env('ENLIGHTN_SKIP_ENVIRONMENT_SPECIFIC', false),

    /*
    |--------------------------------------------------------------------------
    | Guest URL
    |--------------------------------------------------------------------------
    |
    | Specify any guest url or path (preferably your app's login url) here. This
    | would be used by Enlightn to inspect your application HTTP headers.
    | Example: '/login'.
    |
     */
    'guest_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Exclusions From Reporting
    |--------------------------------------------------------------------------
    |
    | Specify the analyzer classes that you wish to exclude from reporting. This
    | means that if any of these analyzers fail, they will not be counted
    | towards the exit status of the Enlightn command. This is useful
    | if you wish to run the command in your CI/CD pipeline.
    | Example: [\Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class].
    |
     */
    'dont_report' => [],

    /*
    |--------------------------------------------------------------------------
    | Ignoring Errors
    |--------------------------------------------------------------------------
    |
    | Use this config option to ignore specific errors. The key of this array
    | would be the analyzer class and the value would be an associative
    | array with path and details. Run php artisan enlightn:baseline
    | to auto-generate this. Patterns are supported in details.
    |
     */

    'ignore_errors' => [

        \Enlightn\Enlightn\Analyzers\Reliability\DeadCodeAnalyzer::class => [
            [
                'path' => 'app/Http/Controllers/Api/DeviceController.php',
                'details' => 'Unreachable statement - code above always terminates.',
            ],
        ],
        \Enlightn\Enlightn\Analyzers\Reliability\DeadCodeAnalyzer::class => [
            [
                'path' => 'app/Http/Controllers/Api/DeviceController.php',
                'details' => 'Unreachable statement - code above always terminates.',
            ],
        ],
        \Enlightn\Enlightn\Analyzers\Reliability\InvalidMethodCallAnalyzer::class => [
            [
                'path' => 'app/Console/Commands/rConfigClearHorizon.php',
                'details' => 'Call to an undefined static method Redis::connection().',
            ],
            [
                'path' => 'app/Http/Controllers/Api/PrivSshKeysController.php',
                'details' => '*Cannot call method*',
            ],
            [
                'path' => 'app/Http/Controllers/Auth/LoginController.php',
                'details' => '*Result of method*',
            ],
        ],
        \Enlightn\Enlightn\Analyzers\Performance\CollectionCallAnalyzer::class => [
            [
                'path' => 'app/Console/Commands/rConfigActivityLogArchive.php',
                'details' => '*retrieved as a query*',
            ],
            [
                'path' => 'app/Console/Commands/rConfigReportLastDownload.php',
                'details' => '*retrieved as a query*',
            ],
            [
                'path' => 'app/Console/Commands/rconfigSearchConfigs.php',
                'details' => '*retrieved as a query*',
            ],
            [
                'path' => 'app/CustomClasses/SaveConfigsToDiskAndDb.php',
                'details' => '*retrieved as a query*',
            ],
            [
                'path' => 'app/Http/Controllers/Api/DashboardController.php',
                'details' => '*retrieved as a query*',
            ],
            [
                'path' => 'app/Http/Controllers/Api/DashboardController.php',
                'details' => '*retrieved as a query*',
            ],
        ],
        \Enlightn\Enlightn\Analyzers\Reliability\InvalidPropertyAccessAnalyzer::class => [
            [
                'path' => 'app/Console/Commands/rconfigTaskDownload.php',
                'details' => '* undefined property*',
            ],
            [
                'path' => 'app/CustomClasses/FileOperations.php',
                'details' => '* undefined property*',
            ],
            [
                'path' => 'app/CustomClasses/PurgeOperations.php',
                'details' => '*$username*',
            ],
            [
                'path' => 'app/Jobs/ArchiveLogsJob.php',
                'details' => '*$username*',
            ],
            [
                'path' => 'app/Jobs/PurgeConfigsJob.php',
                'details' => '*$username*',
            ],
            [
                'path' => 'app/Http/Controllers/Api/BackupController.php',
                'details' => '*$name*',
            ],
            [
                'path' => 'app/Http/Controllers/Api/ConfigController.php',
                'details' => '*$name*',
            ],
            [
                'path' => 'app/Http/Controllers/Api/TaskController.php',
                'details' => '*$name*',
            ],
            [
                'path' => 'app/Http/Controllers/Auth/LoginController.php',
                'details' => '*$email*',
            ],
            [
                'path' => 'app/Http/Controllers/Connections/SSH/Connect.php',
                'details' => '*$setWindowSize*',
            ],
            [
                'path' => 'app/Http/Controllers/Connections/SSH/Connect.php',
                'details' => '*$setTerminalDimensions*',
            ],
            [
                'path' => 'app/Http/Controllers/Connections/Telnet/Quit.php',
                'details' => '*$devicePrompt*',
            ],
        ],
        Enlightn\Enlightn\Analyzers\Security\FillableForeignKeyAnalyzer::class => [
            [
                'path' => 'app/Models/Device.php',
                'details' => 'Potential foreign key device_category_id declared as fillable and available for mass assignment.',
            ],
        ],
        Enlightn\Enlightn\Analyzers\Reliability\UndefinedVariableAnalyzer::class => [
            [
                'path' => 'app/CustomClasses/CronSchedule.php',
                'details' => 'Variable $intNumber2 might not be defined.',
            ],
            [
                'path' => 'app/Http/Controllers/Connections/Telnet/SendCommand.php',
                'details' => 'Undefined variable: $result',
            ],
        ],
        Enlightn\Enlightn\Analyzers\Reliability\MissingReturnStatementAnalyzer::class => [
            [
                'path' => 'app/Http/Middleware/Authenticate.php',
                'details' => 'Method App\Http\Middleware\Authenticate::redirectTo() should return string but return statement is missing.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analyzer Configurations
    |--------------------------------------------------------------------------
    |
    | The following configuration options pertain to individual analyzers.
    | These are recommended options but feel free to customize them based
    | on your application needs.
    |
     */
    'license_whitelist' => [
        'Apache-2.0', 'Apache2', 'BSD-2-Clause', 'BSD-3-Clause', 'LGPL-2.1-only', 'LGPL-2.1',
        'LGPL-2.1-or-later', 'LGPL-3.0', 'LGPL-3.0-only', 'LGPL-3.0-or-later', 'MIT', 'ISC',
        'CC0-1.0', 'Unlicense', 'WTFPL', 'GPL-2.0-only', 'GPL-2.0-or-later', 'GPL-3.0-only',
    ],

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | The following credentials are used to share your Enlightn report with
    | the Enlightn Github Bot. This allows the bot to compile the report
    | and add review comments on your pull requests.
    |
     */
    'credentials' => [
        'username' => env('ENLIGHTN_USERNAME'),
        'api_token' => env('ENLIGHTN_API_TOKEN'),
    ],

    // Set this value to your Github repo for integrating with the Enlightn Github Bot
    // Format: "myorg/myrepo" like "laravel/framework".
    'github_repo' => env('ENLIGHTN_GITHUB_REPO'),

    // Set to true to restrict the max number of files displayed in the enlightn
    // command for each check. Set to false to display all files.
    'compact_lines' => true,

    // List your commercial packages (licensed by you) below, so that they are not
    // flagged by the License Analyzer.
    'commercial_packages' => [
        'enlightn/enlightnpro',
    ],

    'allowed_permissions' => [
        base_path() => '775',
        app_path() => '775',
        resource_path() => '775',
        storage_path() => '775',
        public_path() => '775',
        config_path() => '775',
        database_path() => '775',
        base_path('routes') => '775',
        app()->bootstrapPath() => '775',
        app()->bootstrapPath('cache') => '775',
        app()->bootstrapPath('app.php') => '664',
        base_path('artisan') => '775',
        public_path('index.php') => '664',
        public_path('server.php') => '664',
    ],

    'writable_directories' => [
        storage_path(),
        app()->bootstrapPath('cache'),
    ],
];
