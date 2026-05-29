<?php

namespace Database\Seeders\testdata;

use Database\Seeders\testdata\Devices\AddCategories;
use Database\Seeders\testdata\Devices\AddCommands;
use Database\Seeders\testdata\Devices\AddTags;
use Database\Seeders\testdata\Devices\AddTasks;
use Database\Seeders\testdata\Devices\AddTemplates;
use Database\Seeders\testdata\Devices\MikrotikDeviceSeeder;
use Database\Seeders\testdata\Devices\Router10Seed;
use Database\Seeders\testdata\Devices\Router11Seed;
use Database\Seeders\testdata\Devices\Router1Seed;
use Database\Seeders\testdata\Devices\Router2Seed;
use Database\Seeders\testdata\Devices\Router3Seed;
use Database\Seeders\testdata\Devices\Router4Seed;
use Database\Seeders\testdata\Devices\Router5Seed;
use Database\Seeders\testdata\Devices\Router6Seed;
use Database\Seeders\testdata\Devices\Router8Seed;
use Database\Seeders\testdata\Devices\Router9Seed;
use Illuminate\Database\Seeder;

class DeviceTableSeeder extends Seeder
{
    // NOTE: This is only used for running tests

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AddCategories::class,
            AddCommands::class,
            AddTemplates::class,
            AddTags::class,
            AddTasks::class,
            Router1Seed::class,
            Router2Seed::class,
            Router3Seed::class,
            Router4Seed::class,
            Router5Seed::class,
            Router6Seed::class,
            Router8Seed::class,
            Router9Seed::class,
            Router10Seed::class,
            Router11Seed::class,
            MikrotikDeviceSeeder::class,
        ]);
    }
}
