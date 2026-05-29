<?php

namespace Database\Seeders\testdata\Devices;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Router4Seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dev_cisco_ip = '10.1.1.170';
        $id = 1004;

        // SSH enable template
        Device::create([
            'id' => $id,
            'device_name' => 'router4',
            'device_ip' => $dev_cisco_ip,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco1',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'router1#',
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 4,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
            'last_seen' => '2024-05-10 13:57:49',
            'created_at' => '2024-05-10 10:34:20',
            'updated_at' => '2024-05-10 10:57:49',
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
            'template_id' => 4,
            'device_id' => $id,
        ]);

        DB::table('device_tag')->insert([
            'tag_id' => 1,
            'device_id' => $id,
        ]);
    }
}
