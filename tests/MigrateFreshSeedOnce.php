<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

trait MigrateFreshSeedOnce
{
    /**
     * If true, setup has run at least once.
     *
     * @var bool
     */
    protected static $setUpHasRunOnce = false;

    /**
     * After the first run of setUp "migrate:fresh --seed"
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            Artisan::call(
                'db:seed',
                ['--class' => 'TestdataDatabaseSeeder']
            );

            static::$setUpHasRunOnce = true;
        }
    }
}
