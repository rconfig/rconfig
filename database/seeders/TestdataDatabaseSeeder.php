<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestdataDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The lab-hardware device fixtures (testdata\DeviceTableSeeder) used to run here for
     * every suite, but only Slowtests' real SSH/Telnet/ICMP tests are hard-coded against
     * those fixed devices. It's seeded directly for the Slowtests suite instead, in
     * MigrateFreshSeedOnce. Fasttests/Unit build any device/category/tag/etc. fixtures
     * they need via model factories.
     *
     * testdata\HealthCheckResultsSeeder is not called here either: nothing in the test
     * suite reads the health_check_result_history_items rows it inserts, and the table is
     * explicitly excluded from the export feature, so it was dead weight.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            NotificationDefaultsSeeder::class,
        ]);
    }
}
