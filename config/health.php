<?php

use App\HealthChecks\Notifications\HealthAlertResultStore;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\EloquentHealthResultStore;

return [

    /*
     * A result store is responsible for saving the results of the checks. The
     * `EloquentHealthResultStore` will save results in the database. You
     * can use multiple stores at the same time.
     *
     * HealthAlertResultStore must stay first: it compares each result with the
     * previous one in the history table before the Eloquent store saves this run.
     */
    'result_stores' => [
        HealthAlertResultStore::class,

        EloquentHealthResultStore::class => [
            'model' => HealthCheckResultHistoryItem::class,
            'keep_history_for_days' => 30,
        ],

        /*
        Spatie\Health\ResultStores\CacheHealthResultStore::class => [
            'store' => 'file',
        ],

        Spatie\Health\ResultStores\JsonFileHealthResultStore::class => [
            'disk' => 's3',
            'path' => 'health.json',
        ],

        Spatie\Health\ResultStores\InMemoryHealthResultStore::class,
        */
    ],

    /*
     * Spatie's own notifications stay off. Mail settings live in the settings table, not
     * config or .env, so spatie cannot address them. Health alerts are raised by
     * HealthAlertResultStore through rConfig's notification channels instead (RCO-1836).
     */
    'notifications' => [
        'enabled' => false,
    ],

    /*
     * You can let Oh Dear monitor the results of all health checks. This way, you'll
     * get notified of any problems even if your application goes totally down. Via
     * Oh Dear, you can also have access to more advanced notification options.
     */
    'oh_dear_endpoint' => [
        'enabled' => false,

        /*
         * When this option is enabled, the checks will run before sending a response.
         * Otherwise, we'll send the results from the last time the checks have run.
         */
        'always_send_fresh_results' => true,

        /*
         * The secret that is displayed at the Application Health settings at Oh Dear.
         */
        'secret' => env('OH_DEAR_HEALTH_CHECK_SECRET'),

        /*
         * The URL that should be configured in the Application health settings at Oh Dear.
         */
        'url' => '/oh-dear-health-check-results',
    ],

    /*
     * You can set a theme for the local results page
     *
     * - light: light mode
     * - dark: dark mode
     */
    'theme' => 'light',

    /*
     * Configure the connectivity ping check.
     *
     * - Set `HEALTHCHECK_PING_ENABLED=false` to disable the check entirely.
     * - Set `HEALTHCHECK_OFFLINE_MODE=true` or `HEALTHCHECK_AIR_GAPPED=true`
     *   to automatically skip external ping checks.
     * - Set `HEALTHCHECK_PING_TARGET` to an internal endpoint/host such as
     *   `http://127.0.0.1`, `http://localhost`, or your gateway address.
     */
    'ping' => [
        'enabled' => env('HEALTHCHECK_PING_ENABLED', true),
        'offline_mode' => env('HEALTHCHECK_OFFLINE_MODE', false),
        'air_gapped' => env('HEALTHCHECK_AIR_GAPPED', false),
        'target' => env('HEALTHCHECK_PING_TARGET', 'https://www.rconfig.com'),
        'timeout' => (int) env('HEALTHCHECK_PING_TIMEOUT', 5),
    ],
];
