<?php

namespace Database\Seeders\testdata\Devices;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Router9Seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dev_cisco_ipv6 = '2a01:ac:1000:700::170';
        $id = 1009;

        Device::create([
            'id' => $id,
            'device_name' => 'router1v6',
            'device_ip' => $dev_cisco_ipv6,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'router1#',
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 8,
            'device_template' => 1,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
            'last_seen' => '2023-05-10 13:57:49',
            'created_at' => '2023-05-10 10:34:20',
            'updated_at' => '2023-05-10 10:57:49',
        ]);

        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => $id,
        ]);

        DB::table('device_vendor')->insert([
            'vendor_id' => 4,
            'device_id' => $id,
        ]);

        DB::table('device_template')->insert([
            'template_id' => 1,
            'device_id' => $id,
        ]);
    }
}
