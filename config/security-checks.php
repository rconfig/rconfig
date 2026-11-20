<?php

use App\Services\Security\Checks;

/*
|--------------------------------------------------------------------------
| Security Review
|--------------------------------------------------------------------------
|
| Settings for php artisan rconfig:security-check and Settings > Security
| Review. Every check is read-only.
|
*/

return [

    'docs_url' => 'https://v8coredocs.rconfig.com/security/security-review/',

    // Check reference pages: <checks_docs_url>/<group>/#<check-id-with-hyphens>.
    'checks_docs_url' => 'https://v8coredocs.rconfig.com/security/checks',

    'advisories_url' => 'https://www.rconfig.com/security-advisories',

    // Daily scheduled run (24h HH:MM, server time), on the queue below.
    'schedule_enabled' => env('SECURITY_CHECK_SCHEDULE', true),
    'schedule_time' => env('SECURITY_CHECK_TIME', '04:15'),
    'queue' => 'rConfigDefault',

    // Default for the "Email me when a check starts failing" switch until it is saved on the page.
    'notify' => env('SECURITY_CHECK_NOTIFY', true),

    'probes' => [
        'enabled' => env('SECURITY_CHECK_PROBES', true),
        'timeout' => 3,
        // Extra hosts to probe besides 127.0.0.1, this server's addresses and the APP_URL host.
        'extra_targets' => array_values(array_filter(explode(',', (string) env('SECURITY_CHECK_PROBE_TARGETS', '')))),
    ],

    'thresholds' => [
        'tls_expiry_fail_days' => 14,
        'tls_expiry_warn_days' => 30,
        'api_token_max_age_days' => (int) env('SECURITY_CHECK_API_TOKEN_MAX_AGE_DAYS', 365),
        'max_login_attempts' => 10,
    ],

    /*
    | Every check, in display order within its group. IDs are stable: the docs refer to them.
    */
    'checks' => [
        // Application
        Checks\App\AppDebugCheck::class,
        Checks\App\AppEnvCheck::class,
        Checks\App\AppKeyCheck::class,
        Checks\App\AppUrlHttpsCheck::class,
        Checks\App\EnvFilePermissionsCheck::class,

        // Web exposure (self-probe)
        Checks\Web\EnvExposedCheck::class,
        Checks\Web\StorageExposedCheck::class,
        Checks\Web\VcsExposedCheck::class,
        Checks\Web\RegistrationClosedCheck::class,
        Checks\Web\HorizonGuestCheck::class,
        Checks\Web\LogViewerGuestCheck::class,

        // TLS
        Checks\Tls\CertificateExpiryCheck::class,

        // PHP
        Checks\Php\PhpVersionMinimumCheck::class,
        Checks\Php\DisplayErrorsCheck::class,

        // Redis
        Checks\Redis\RedisAuthCheck::class,

        // Accounts and sign-in
        Checks\Accounts\DefaultPasswordCheck::class,
        Checks\Accounts\LoginThrottleCheck::class,

        // REST API
        Checks\Api\RestApiTokensCheck::class,

        // Data at rest
        Checks\Data\CredentialsEncryptedCheck::class,
        Checks\Data\ConfigFileModesCheck::class,

        // Runtime and privileges
        Checks\Runtime\WorkerUserCheck::class,
        Checks\Runtime\SchedulerHeartbeatCheck::class,
        Checks\Runtime\QueueWorkersCheck::class,

        // Versions
        Checks\Versions\RconfigLatestCheck::class,

        // Devices
        Checks\Devices\TelnetDevicesCheck::class,

        // Operating system
        Checks\Os\FirewallCheck::class,
    ],

];
