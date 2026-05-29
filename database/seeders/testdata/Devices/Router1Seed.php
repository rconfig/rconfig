<?php

namespace Database\Seeders\testdata\Devices;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Router1Seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dev_cisco_ip = '10.1.1.170';
        $id = 1001;

        // telnet no enable template
        Device::create([
            'id' => $id,
            'device_name' => 'router1',
            'device_ip' => $dev_cisco_ip,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => '1#', // reduced for the new telnet readTo preg_match
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 1,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
            'last_seen' => '2024-05-10 13:57:49',
            'created_at' => '2024-05-10 13:34:20',
            'updated_at' => '2024-05-10 13:57:49',
        ]);

        DB::table('device_template')->insert([
            'template_id' => 1,
            'device_id' => $id,
        ]);

        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => $id,
        ]);

        DB::table('device_vendor')->insert([
            'vendor_id' => 4,
            'device_id' => $id,
        ]);

        DB::table('tags')->insert([
            'id' => $id,
            'tagname' => 'devtag1',
            'tagDescription' => 'test tag description 1',
        ]);

        DB::table('device_tag')->insert([
            'device_id' => $id,
            'tag_id' => $id,
        ]);

        DB::table('device_task')->insert([
            'device_id' => $id,
            'task_id' => 555555,
        ]);
    }
}
