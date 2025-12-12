<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\HealthChecks\RcDiskSpaceCheck;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Facades\Health;

class HealthCheckProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Health::checks([
            DatabaseCheck::new(),
            CacheCheck::new(),
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),
            HorizonCheck::new(),
            PingCheck::new()->failureMessage('Pinging rconfig.com failed')->url('https://www.rconfig.com')->timeout(5),
            RedisCheck::new(),
            ScheduleCheck::new()->heartbeatMaxAgeInMinutes(10),
            RcDiskSpaceCheck::new()
                ->name('Disk Space: /storage')
                ->filesystemName(storage_path())
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
        ]);
    }
}
