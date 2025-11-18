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
        // \Enlightn\Enlightn\Analyzers\Performance\EnvCallAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\RouteCachingAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\CustomErrorPageAnalyzer::class,
        // \Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class,
        // \Enlightn\Enlightn\Analyzers\Security\PHPIniAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\FilePermissionsAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\QueueTimeoutAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\UndefinedVariableAnalyzer::class,
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

    'ignore_errors' => [Enlightn\Enlightn\Analyzers\Performance\CollectionCallAnalyzer::class => [['path' => 'app/Console/Commands/rConfigActivityLogArchive.php', 'details' => 'Called \'take\' on Laravel collection, but could have been retrieved as a query.'], ['path' => 'app/Console/Commands/rConfigReportLastDownload.php', 'details' => 'Called \'where\' on Laravel collection, but could have been retrieved as a query.']], Enlightn\Enlightn\Analyzers\Reliability\ForeachIterableAnalyzer::class => [['path' => 'app/Http/Controllers/Api/CommandController.php', 'details' => 'Argument of an invalid type array<int, string>|string supplied for foreach, only iterables are supported.'], ['path' => 'app/Http/Controllers/Api/TftpDeviceController.php', 'details' => 'Argument of an invalid type array<int, string>|string supplied for foreach, only iterables are supported.'], ['path' => 'app/Http/Controllers/LanguageController.php', 'details' => 'Argument of an invalid type array<int, string>|false supplied for foreach, only iterables are supported.']], Enlightn\Enlightn\Analyzers\Reliability\InvalidMethodCallAnalyzer::class => [['path' => 'app/Console/TermwindHelpers.php', 'details' => 'Result of function Termwind\\render (void) is used.'], ['path' => 'app/Console/TermwindHelpers.php', 'details' => 'Result of function Termwind\\render (void) is used.'], ['path' => 'app/Console/TermwindHelpers.php', 'details' => 'Result of function Termwind\\render (void) is used.'], ['path' => 'app/Services/Compliance/CreateComplianceReport.php', 'details' => 'Call to method save() on an unknown class App\\Models\\ComplianceReport.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Cannot call method count() on App\\Models\\EocDefinition|int<min, -1>|int<1, max>.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Cannot call method count() on App\\Models\\EocDefinition|int<min, -1>|int<1, max>.'], ['path' => 'database/migrations/2022_02_12_162838_create_health_tables.php', 'details' => 'Call to an undefined method object::getTable().'], ['path' => 'database/migrations/2022_02_12_162838_create_health_tables.php', 'details' => 'Call to an undefined method object::getTable().']], Enlightn\Enlightn\Analyzers\Reliability\InvalidOffsetAnalyzer::class => [['path' => 'app/Services/Connections/Api/ApiConnectionManager.php', 'details' => 'Cannot assign offset \'endpoint_id\' to array<string, mixed>|string.'], ['path' => 'app/Services/Connections/Api/ApiConnectionManager.php', 'details' => 'Cannot assign offset \'endpoint_id\' to array<string, mixed>|string.']], Enlightn\Enlightn\Analyzers\Reliability\InvalidPropertyAccessAnalyzer::class => [['path' => 'app/Http/Controllers/Api/ApiConnectionController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/ApiConnectionController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/ConfigController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/ConfigController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/ConfigController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/ConfigController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/ConfigController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$device_id.'], ['path' => 'app/Http/Controllers/Api/DeviceController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$device_ip.'], ['path' => 'app/Http/Controllers/Api/DeviceController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/DeviceController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/DeviceController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/EocDefinitionController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/EocDefinitionController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/EocDefinitionController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/IntegrationConfiguredController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$integrationoption.'], ['path' => 'app/Http/Controllers/Api/IntegrationConfiguredController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$integration_id.'], ['path' => 'app/Http/Controllers/Api/IntegrationsZabbixController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/IntegrationsZabbixController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$name.'], ['path' => 'app/Http/Controllers/Api/IntegrationsZabbixController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/IntegrationsZabbixController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$name.'], ['path' => 'app/Http/Controllers/Api/SettingEmailController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$mail_to_email.'], ['path' => 'app/Http/Controllers/Api/SnippetController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/SnippetController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/TaskController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$id.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$config_location.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$device_id.'], ['path' => 'app/Http/Controllers/Api/TemplateController.php', 'details' => 'Access to an undefined property Illuminate\\Http\\Response::$fileName.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Access to protected property App\\Models\\ApiEndpoint::$connection.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Cannot access property $id on string.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Access to protected property App\\Models\\ApiEndpoint::$connection.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Cannot access property $id on string.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Access to protected property App\\Models\\ApiEndpoint::$connection.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Cannot access property $name on string.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$main.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$main.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/SSH/SendSnippet.php', 'details' => 'Access to an undefined property object::$snippet.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$main.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$main.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$connect.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$auth.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$config.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Connect.php', 'details' => 'Access to an undefined property object::$deviceparams.'], ['path' => 'app/Services/Connections/Telnet/Login.php', 'details' => 'Access to an undefined property object::$connection.'], ['path' => 'app/Services/Connections/Telnet/Quit.php', 'details' => 'Access to an undefined property object::$connection.'], ['path' => 'app/Services/Connections/Telnet/Quit.php', 'details' => 'Access to an undefined property object::$devicePrompt.'], ['path' => 'app/Services/Connections/Telnet/Quit.php', 'details' => 'Access to an undefined property object::$devicePrompt.'], ['path' => 'app/Services/Connections/Telnet/SendCommand.php', 'details' => 'Access to an undefined property object::$connection.'], ['path' => 'app/Services/Connections/Telnet/SendSnippet.php', 'details' => 'Access to an undefined property object::$connection.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Cannot access property $id on App\\Models\\EocDefinition|int<min, -1>|int<1, max>.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$id.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$name.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$email.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$token.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$refreshToken.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$email.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$id.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$token.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$refreshToken.'], ['path' => 'app/Services/SocialAuth/MicrosoftAuth.php', 'details' => 'Access to an undefined property Laravel\\Socialite\\Contracts\\User::$email.']], Enlightn\Enlightn\Analyzers\Reliability\InvalidReturnTypeAnalyzer::class => [['path' => 'app/Console/Commands/EnvironmentSetCommand.php', 'details' => 'Method App\\Console\\Commands\\EnvironmentSetCommand::parseCommandArguments() should return array<string> but returns array<int, string|false|null>.'], ['path' => 'app/Http/Controllers/Api/CategoryController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\CategoryController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/CommandController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\CommandController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/ConfigChangeController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\ConfigChangeController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/ConfigChangeController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\ConfigChangeController::show() should return Illuminate\\Http\\Response but returns App\\Models\\ConfigChange|null.'], ['path' => 'app/Http/Controllers/Api/EocDefinitionController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\EocDefinitionController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/IntegrationConfiguredController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\IntegrationConfiguredController::show() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/PolicyAssignmentController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\PolicyAssignmentController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/PolicyComplianceReportController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\PolicyComplianceReportController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/PolicyDefinitionController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\PolicyDefinitionController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/RestApiTokenController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\RestApiTokenController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/RoleController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\RoleController::destroy() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/SnippetController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\SnippetController::show() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/TagController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\TagController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\TaskReportController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/TaskReportController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\TaskReportController::show() should return Illuminate\\Http\\Response but returns string.'], ['path' => 'app/Http/Controllers/Api/TemplateController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\TemplateController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/TemplateController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\TemplateController::show() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/UserController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\UserController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/UserLogActivityController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\UserLogActivityController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.'], ['path' => 'app/Http/Controllers/Api/VendorController.php', 'details' => 'Method App\\Http\\Controllers\\Api\\VendorController::index() should return Illuminate\\Http\\Response but returns Illuminate\\Http\\JsonResponse.']], Enlightn\Enlightn\Analyzers\Reliability\MissingModelRelationAnalyzer::class => [['path' => 'app/Console/Commands/SweepTftpDir.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Console/Commands/rConfigPolicyCompliance.php', 'details' => 'Relation \'command\' is not found in App\\Models\\PolicyAssignment model.'], ['path' => 'app/Http/Controllers/Api/ApiCredentialController.php', 'details' => 'Relation \'credential\' is not found in App\\Models\\ApiConnection model.'], ['path' => 'app/Http/Controllers/Api/PolicyAssignmentController.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Http/Controllers/Api/PolicyAssignmentController.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Http/Controllers/Api/TftpDeviceController.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Jobs/EocRecheckDeviceJob.php', 'details' => 'Relation \'devices\' is not found in App\\Models\\EocDefinition model.'], ['path' => 'app/Providers/RbacServiceProvider.php', 'details' => 'Relation \'roles\' is not found in App\\Models\\Permission model.'], ['path' => 'app/Rules/CategoryHasCommands.php', 'details' => 'Relation \'command\' is not found in App\\Models\\Category model.'], ['path' => 'app/Services/Config/SaveApiOutputToDiskAndDbService.php', 'details' => 'Relation \'connection\' is not found in App\\Models\\ApiEndpoint model.'], ['path' => 'app/Services/Connections/Api/GetAndCheckApiConnectionIds.php', 'details' => 'Relation \'category\' is not found in App\\Models\\ApiConnection model.'], ['path' => 'app/Services/Connections/DeviceRecordPrepare.php', 'details' => 'Relation \'command\' is not found in App\\Models\\Category model.'], ['path' => 'app/Services/Connections/GetAndCheckCategoryIds.php', 'details' => 'Relation \'device\' is not found in App\\Models\\Category model.'], ['path' => 'app/Services/Connections/GetAndCheckDeviceIds.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Services/Connections/GetAndCheckTagIds.php', 'details' => 'Relation \'device\' is not found in App\\Models\\Tag model.'], ['path' => 'app/Services/Connections/GetAndCheckTaskIds.php', 'details' => 'Relation \'device\' is not found in App\\Models\\Task model.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Relation \'devices\' is not found in App\\Models\\EocDefinition model.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Relation \'category\' is not found in App\\Models\\Device model.'], ['path' => 'app/Services/Eoc/EocCheckService.php', 'details' => 'Relation \'Command\' is not found in App\\Models\\EocDefinition model.']], Enlightn\Enlightn\Analyzers\Reliability\MissingReturnStatementAnalyzer::class => [['path' => 'app/Notifications/ConfigChangedMailNotification.php', 'details' => 'Method App\\Notifications\\ConfigChangedMailNotification::toMail() should return Illuminate\\Notifications\\Messages\\MailMessage but return statement is missing.'], ['path' => 'app/Services/Integrations/Zbx/Zbx6LoadToProd.php', 'details' => 'Method App\\Services\\Integrations\\Zbx\\Zbx6LoadToProd::load() should return int but return statement is missing.']]],

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
