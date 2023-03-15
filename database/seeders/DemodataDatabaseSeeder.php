<?php

namespace Database\Seeders;

use Database\Seeders\demodata\DemoCategorySeeder;
use Database\Seeders\demodata\DemoCommandSeeder;
use Database\Seeders\demodata\DemoDeviceSeeder;
use Database\Seeders\demodata\DemoTagSeeder;
use Database\Seeders\demodata\DemoTaskReportSeeder;
use Database\Seeders\demodata\DemoTaskSeeder;
use Database\Seeders\demodata\DemoTemplateSeeder;
use Database\Seeders\demodata\DemoUserSeeder;
use Illuminate\Database\Seeder;

// php artisan migrate:fresh && php artisan db:seed --class=DemodataDatabaseSeeder
class DemodataDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DemoUserSeeder::class,
            DemoTagSeeder::class,
            DemoCommandSeeder::class,
            DemoCategorySeeder::class,
            DemoTemplateSeeder::class,
            DemoDeviceSeeder::class,
            DemoTaskSeeder::class,
            DemoTaskReportSeeder::class,
        ]);
    }
}

// TODO
// ACTIVITY LOGS
// REMOVE BACKUPS
