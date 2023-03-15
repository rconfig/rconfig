<?php

namespace Database\Seeders\testdata;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MikrotikDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $device_id = 20001;
        $cat_id = 20001;
        $command_id = 5189;
        $template_id = $faker->randomNumber(4);
        $template_url = 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Mikrotik/mikrotek-ssh-noenable.yml';
        $template_contents = file_get_contents($template_url);
        $filename = basename($template_url);
        File::put(templates_path() . $filename, $template_contents);

        DB::table('categories')->insert([
            'id' => $cat_id,
            'categoryName' => 'mikrotik',
            'categoryDescription' => null,
            'badgeColor' => 'badge-primary',
            'created_at' => '2020-06-06 22:20:44',
            'updated_at' => null,
        ]);

        DB::table('commands')->insert([
            'id' => $command_id,
            'command' => 'export',
        ]);
        DB::table('commands')->insert([
            'id' => $command_id + 1,
            'command' => 'interface print',
        ]);
        DB::table('vendors')->insert([
            'id' => 20001,
            'vendorName' => 'mikrotik',
        ]);

        DB::table('category_device')->insert([
            'category_id' => $cat_id,
            'device_id' => $device_id,
        ]);

        DB::table('category_command')->insert([
            'category_id' => $cat_id,
            'command_id' => $command_id,
        ]);

        DB::table('category_command')->insert([
            'category_id' => $cat_id,
            'command_id' => $command_id + 1,
        ]);

        DB::table('templates')->insert([
            'id' => $template_id,
            'fileName' => '/app/rconfig/templates/' . $filename,
        ]);

        DB::table('device_template')->insert([
            'template_id' => $template_id,
            'device_id' => $device_id,
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 20001,
            'device_id' => $device_id,
        ]);

        // SSH no enable template
        Device::create([
            'id' => $device_id,
            'device_name' => 'mikrotik',
            'device_ip' => '192.168.1.188',
            'device_default_creds_on' => 0,
            'device_username' => 'admin+cte',
            'device_password' => 'nokia5110',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => '[admin@routermikrotik] >',
            'device_enable_prompt' => '',
            'device_category_id' => $cat_id,
            'device_template' => $template_id,
            'device_model' => 'Mikrotik',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);
    }
}
