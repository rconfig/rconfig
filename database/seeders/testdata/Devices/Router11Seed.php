<?php

namespace Database\Seeders\testdata\Devices;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Router11Seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $id = 1011;

        // Linux Private Key
        Device::create([
            'id' => $id,
            'device_name' => 'homedev.rconfig.com',
            'device_ip' => '192.168.1.2',
            'device_default_creds_on' => 0,
            'device_username' => 'root',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => '*', // mimics a partial cli prompt match - test and working
            'device_enable_prompt' => '*',
            'device_category_id' => 11,
            'device_template' => 11,
            'device_model' => 'centos8',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
            'ssh_key_id' => 1,
            'last_seen' => '2023-05-10 13:57:49',
            'created_at' => '2023-05-10 10:34:20',
            'updated_at' => '2023-05-10 10:57:49',
        ]);

        DB::table('category_device')->insert([
            'category_id' => 11,
            'device_id' => $id,
        ]);

        DB::table('device_template')->insert([
            'template_id' => 11,
            'device_id' => $id,
        ]);

        DB::table('device_vendor')->insert([
            'vendor_id' => 2,
            'device_id' => $id,
        ]);

        DB::table('device_tag')->insert([
            'tag_id' => 1,
            'device_id' => $id,
        ]);
    }
}
