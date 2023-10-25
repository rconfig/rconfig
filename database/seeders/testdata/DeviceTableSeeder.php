<?php

namespace Database\Seeders\testdata;

use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceTableSeeder extends Seeder
{
    // NOTE: This is only used for running tests

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dev_cisco_ip = '192.168.1.170';
        $dev_cisco_ipv6 = '2001:BB6:788A:8000:20C:29FF:FE6D:A267';
        // telnet no enable template
        Device::create([
            'id' => 1001,
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
        ]);

        // // telnet enable template
        Device::create([
            'id' => 1002,
            'device_name' => 'router2',
            'device_ip' => $dev_cisco_ip,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco1',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'router1#',
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 2,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);

        // SSH no enable template
        Device::create([
            'id' => 1003,
            'device_name' => 'router3',
            'device_ip' => 'router1.rconfig.com',
            'device_default_creds_on' => 0,
            'device_username' => 'cisco',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'r1#', // mimics a partial cli prompt match - test and working
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 3,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);


        // SSH enable template
        Device::create([
            'id' => 1004,
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
        ]);

        // Unreachable Device
        Device::create([
            'id' => 1005,
            'device_name' => 'router5',
            'device_ip' => '192.169.1.1',
            'device_default_creds_on' => 0,
            'device_username' => 'cisco1',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'router1#',
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 1,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);

        // Device with Options Template Device - unused for most tests
        Device::create([
            'id' => 1006,
            'device_name' => 'router6',
            'device_ip' => $dev_cisco_ip,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'router1#',
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 6000,
            'device_template' => 2000,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);
        Device::create([
            'id' => 1008,
            'device_name' => 'router8',
            'device_ip' => '10.0.0.111',
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
        ]);

        Device::create([
            'id' => 1009,
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
        ]);
        // SSH no enable template IPv6
        Device::create([
            'id' => 1010,
            'device_name' => 'router1v6',
            'device_ip' => $dev_cisco_ipv6,
            'device_default_creds_on' => 0,
            'device_username' => 'cisco',
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
            'device_main_prompt' => 'r1#', // mimics a partial cli prompt match - test and working
            'device_enable_prompt' => 'router1>',
            'device_category_id' => 1,
            'device_template' => 3,
            'device_model' => 'CSR1000v',
            'device_version' => '',
            'device_added_by' => 1,
            'status' => 1,
        ]);
        // Linux Private Key
        Device::create([
            'id' => 1011,
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
        ]);

        DB::table('commands')->insert([
            'id' => 6000,
            'command' => 'dev_cmd',
        ]);
        DB::table('commands')->insert([
            'id' => 11,
            'command' => 'hostname',
        ]);
        DB::table('category_command')->insert([
            'category_id' => 6000,
            'command_id' => 6000,
        ]);
        DB::table('category_command')->insert([
            'category_id' => 8,
            'command_id' => 1,
        ]);
        DB::table('category_command')->insert([
            'category_id' => 11,
            'command_id' => 11,
        ]);

        DB::table('device_template')->insert([
            'template_id' => 1,
            'device_id' => '1001',
        ]);

        DB::table('device_template')->insert([
            'template_id' => 4,
            'device_id' => '1004',
        ]);
        DB::table('device_template')->insert([
            'template_id' => 11,
            'device_id' => '1011',
        ]);
        DB::table('device_template')->insert([
            'template_id' => 2000,
            'device_id' => '1006',
        ]);
        DB::table('device_template')->insert([
            'template_id' => 2000,
            'device_id' => '1008',
        ]);
        DB::table('templates')->insert([
            0 => [
                'id' => '11',
                'fileName' => '/app/rconfig/templates/ssh_priv_key_test.yml',
                'templateName' => 'ssh_priv_key_test',
                'description' => 'ssh_priv_key_test descr',
                'created_at' => '2021-02-27 12:09:44',
                'updated_at' => null,
            ],
        ]);

        // seed categories and relationships
        DB::table('categories')->insert([
            'id' => 1010,
            'categoryName' => 'devcategory10',
            'categoryDescription' => 'This will always be a dummy category - i.e. no devices attached',
        ]);
        DB::table('categories')->insert([
            'id' => 1011,
            'categoryName' => 'devcategory11',
            'categoryDescription' => 'This will always be a dummy category - i.e. no devices attached, but has a command',
        ]);
        DB::table('category_command')->insert([
            'category_id' => 1011,
            'command_id' => 6000,
        ]);

        DB::table('categories')->insert([
            'id' => '8',
            'categoryName' => 'Unreachable_devices',
            'categoryDescription' => null,
            'badgeColor' => 'badge-primary',
            'created_at' => '2018-06-06 22:20:44',
            'updated_at' => null,
        ]);
        DB::table('categories')->insert([
            'id' => 6000,
            'categoryName' => 'dev_cat',
        ]);
        DB::table('categories')->insert([
            'id' => 11,
            'categoryName' => 'dev_linux',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1001',
        ]);

        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1002',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1003',
        ]);

        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1004',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1005',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1009',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 1,
            'device_id' => '1010',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 55,
            'device_id' => '1006',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 8,
            'device_id' => '1008',
        ]);
        DB::table('category_device')->insert([
            'category_id' => 11,
            'device_id' => '1011',
        ]);

        // seed categories and relationships
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1001',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1002',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1003',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1004',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1005',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1006',
        ]);
        DB::table('device_vendor')->insert([
            'vendor_id' => 1,
            'device_id' => '1008',
        ]);

        //seed tags and relationships
        DB::table('tags')->insert([
            'id' => 1001,
            'tagname' => 'devtag1',
            'tagDescription' => 'test tag description 1',
        ]);
        DB::table('tags')->insert([
            'id' => 1002,
            'tagname' => 'devtag2',
            'tagDescription' => 'test tag description 2',
        ]);
        DB::table('tags')->insert([
            'id' => 1003,
            'tagname' => 'devtag3',
            'tagDescription' => 'test tag description 3',
        ]);
        DB::table('tags')->insert([
            'id' => 1010,
            'tagname' => 'devtag10',
            'tagDescription' => 'This will always be a dummy tag - i.e. no devices attached',
        ]);
        DB::table('device_tag')->insert([
            'device_id' => 1001,
            'tag_id' => 1001,
        ]);

        DB::table('device_tag')->insert([
            'device_id' => 1002,
            'tag_id' => 1002,
        ]);
        DB::table('device_tag')->insert([
            'device_id' => 1005,
            'tag_id' => 1003,
        ]);
        DB::table('device_tag')->insert([
            'device_id' => 1006,
            'tag_id' => 55,
        ]);

        //Seed tasks and relationships
        DB::table('tasks')->insert([
            'id' => 555555,
            'task_name' => 'DevTask1',
            'task_desc' => 'DevTask1 Description',
            'task_command' => 'rconfig:download-device',
            'task_categories' => null,
            'task_devices' => 1,
            'task_tags' => null,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 1,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
            'is_system' => 0,
        ]);
        DB::table('tasks')->insert([
            'id' => 666666,
            'task_name' => 'DevTask2',
            'task_desc' => 'DevTask2 Description',
            'task_command' => 'rconfig:download-tag',
            'task_categories' => null,
            'task_devices' => null,
            'task_tags' => 1,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 0,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
            'is_system' => 0,
        ]);
        DB::table('tasks')->insert([
            'id' => 777777,
            'task_name' => 'DevTask3',
            'task_desc' => 'DevTask3 Description',
            'task_command' => 'rconfig:download-category',
            'task_categories' => null,
            'task_devices' => null,
            'task_tags' => 1,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 0,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
            'is_system' => 0,
        ]);
        DB::table('tasks')->insert([
            'id' => 888888,
            'task_name' => 'DevTask4',
            'task_desc' => 'DevTask4 has only unreachable devices',
            'task_command' => 'rconfig:download-category',
            'task_categories' => 8,
            'task_devices' => null,
            'task_tags' => null,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 1,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
            'is_system' => 0,
        ]);
        DB::table('tasks')->insert([
            'id' => 999999,
            'task_name' => 'BackupTask1',
            'task_desc' => 'BackupTask1',
            'task_command' => 'backup:run --only-to-disk=rconfig',
            'task_categories' => 8,
            'task_devices' => null,
            'task_tags' => null,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 0,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
            'is_system' => 0,
        ]);

        DB::table('device_task')->insert([
            'device_id' => 1001,
            'task_id' => 555555,
        ]);
        DB::table('device_task')->insert([
            'device_id' => 1002,
            'task_id' => 555555,
        ]);

        DB::table('tag_task')->insert([
            'tag_id' => 1002,
            'task_id' => 666666,
        ]);
        DB::table('category_task')->insert([
            'category_id' => 1,
            'task_id' => 777777,
        ]);
        DB::table('category_task')->insert([
            'category_id' => 2,
            'task_id' => 777777,
        ]);
        DB::table('category_task')->insert([
            'category_id' => 3,
            'task_id' => 777777,
        ]);
        DB::table('category_task')->insert([
            'category_id' => 8,
            'task_id' => 888888,
        ]);
    }
}
