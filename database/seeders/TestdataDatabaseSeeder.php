<?php

namespace Database\Seeders;

use Database\Seeders\testdata\DeviceTableSeeder;
use Database\Seeders\testdata\HealthCheckResultsSeeder;
use Database\Seeders\testdata\MikrotikDeviceSeeder;
use Illuminate\Database\Seeder;

class TestdataDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DeviceTableSeeder::class,
            HealthCheckResultsSeeder::class,
            MikrotikDeviceSeeder::class,
        ]);
    }
}
