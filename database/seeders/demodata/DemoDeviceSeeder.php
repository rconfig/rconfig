<?php

namespace Database\Seeders\demodata;

use App\Models\Config;
use App\Models\Device;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Activitylog\Models\Activity;

class DemoDeviceSeeder extends Seeder
{
    protected $faker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = \Faker\Factory::create();

        // create an array from 1 - 25
        $d = 1;
        $devices = [];
        for ($d = 1; $d < 26; $d++) {
            // echo "Device $d\n";
            $devices[$d]['id'] = $d;
            $devices[$d]['deviceid'] = 5000 . $d;
            $devices[$d]['device_name'] = 'router' . $d;
            $devices[$d]['deviceip'] = '192.168.50.' . $d . '0';
            $devices[$d]['device_password'] = 'cisco';
            $devices[$d]['device_category_id'] = 1;
            $devices[$d]['templateid'] = 1;
            $devices[$d]['vendorid'] = 1;
            $devices[$d]['tagid'] = 1;
            $devices[$d]['status'] = 1;
            $devices[$d]['datecreated'] = $this->faker->dateTimeBetween('-1 years', 'now');
            $devices[$d]['lastseen'] = $this->faker->dateTimeBetween('-6 months', 'now');
        }

        $this->create_the_devices($devices);
    }

    public function create_the_devices($devices)
    {
        $configFiles = ['showclock_1609.txt', 'showversion_1609.txt', 'showrun_1609.txt', 'Showiproute_846.txt'];
        foreach ($devices as $device) {
            $downloadstatus = 1;

            if (in_array($device['id'], [4, 12, 16, 20, 22])) {
                $device['device_password'] = 'aBadPassword'; // false ip
                $device['status'] = 0;
                $downloadstatus = 0;
            }

            Device::create([
                'id' => $device['deviceid'],
                'device_name' => $device['device_name'],
                'device_ip' => $device['deviceip'],
                'device_default_creds_on' => 0,
                'device_username' => 'cisco',
                'device_password' => $device['device_password'],
                'device_enable_password' => 'cisco',
                'ssh_key_id' => null,
                'device_main_prompt' => 'router1#',
                'device_enable_prompt' => 'router1>',
                'device_category_id' => $device['device_category_id'],
                'device_template' => $device['templateid'],
                'device_model' => 'CSR1000v',
                'device_version' => '',
                'device_added_by' => '1',
                'created_at' => $device['datecreated'],
                'updated_at' => $device['datecreated'],
                'status' => $device['status'],
                'last_seen' => $device['lastseen'],
            ]);
            DB::table('device_template')->insert(
                [
                    'device_id' => $device['deviceid'],
                    'template_id' => $device['templateid'],
                ],
            );
            DB::table('category_device')->insert(
                [
                    'category_id' => $device['device_category_id'],
                    'device_id' => $device['deviceid'],
                ],
            );
            DB::table('device_vendor')->insert(
                [
                    'vendor_id' => $device['vendorid'],
                    'device_id' => $device['deviceid'],
                ],
            );
            DB::table('device_tag')->insert(
                [
                    'tag_id' => $device['tagid'],
                    'device_id' => $device['deviceid'],
                ],
            );
            DB::table('device_tag')->insert(
                [
                    'tag_id' => $this->faker->randomElement([4, 5, 6]),
                    'device_id' => $device['deviceid'],
                ],
            );

            for ($j = 0; $j < $this->faker->numberBetween(50, 100); $j++) {
                $datetime = $this->faker->dateTimeBetween('-' . $j . ' weeks', '-' . $j - 1 . ' weeks');

                foreach ($this->activity_log($device['deviceid'], $device['device_name'], $datetime) as $log) {
                    Activity::insert([$log]);
                }
                foreach ($this->configs($device['deviceid'], $device['device_name'], $datetime, $configFiles, $downloadstatus) as $config) {
                    Config::insert([$config]);
                }
            }

            foreach ($configFiles as $configFile) {
                if (!File::exists(config_data_path() . $configFile)) {
                    File::copy(rconfig_appdir_path() . '/database/seeders/demodata/democonfigs/' . $configFile, config_data_path() . $configFile);
                }
            }
        }
    }

    public function configs($deviceid, $device_name, $datetime, $configFiles, $downloadstatus = 1)
    {
        if ($downloadstatus === 0) {
            $configFiles = [];
        }

        return [
            [
                'device_id' => $deviceid,
                'device_name' => $device_name,
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 2,
                'report_id' => null,
                'config_location' => !empty($configFiles) ? config_data_path() . $configFiles[0] : null,
                'config_filename' => 'showclock_1609.txt',
                'config_filesize' => 33,
                'start_time' => $datetime,
                'end_time' => $datetime,
                'duration' => 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'device_id' => $deviceid,
                'device_name' => $device_name,
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => !empty($configFiles) ? config_data_path() . $configFiles[1] : null,
                'config_filename' => 'showversion_1609.txt',
                'config_filesize' => 2353,
                'start_time' => $datetime,
                'end_time' => $datetime,
                'duration' => 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'device_id' => $deviceid,
                'device_name' => $device_name,
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => !empty($configFiles) ? config_data_path() . $configFiles[2] : null,
                'config_filename' => 'showrun_1609.txt',
                'config_filesize' => 6129,
                'start_time' => $datetime,
                'end_time' => $datetime,
                'duration' => 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'device_id' => $deviceid,
                'device_name' => $device_name,
                'device_category' => 'Routers',
                'command' => 'show ip route',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => !empty($configFiles) ? config_data_path() . $configFiles[3] : null,
                'config_filename' => 'Showiproute_846.txt',
                'config_filesize' => 2405,
                'start_time' => $datetime,
                'end_time' => $datetime,
                'duration' => 1,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
        ];
    }

    public function activity_log($deviceid, $device_name, $datetime)
    {
        return  [
            [
                'log_name' => 'info',
                'description' => 'Manual config download job completed for Device ID:' . $deviceid,
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'config',
                'device_name' => null,
                'device_id' => null,
                'events_ids' => null,
                'connection_category' => null,
                'connection_ids' => 'N;',
                'class' => 'App\\Jobs\\DownloadConfigNow',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'CLI Download ended for rconfig:download-device IDs:' . $deviceid,
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => '',
                'device_id' => '',
                'events_ids' => null,
                'connection_category' => 'rconfig:download-device',
                'connection_ids' => 'a:1:{i:0;s:5:"' . $deviceid . '";}',
                'class' => 'App\\Console\\Commands\\rconfigDeviceDownload',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'Config downloaded for ' . $device_name . ' with command: "show run" was successful',
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => $device_name,
                'device_id' => $deviceid,
                'events_ids' => null,
                'connection_category' => 'device',
                'connection_ids' => 'N;',
                'class' => 'Illuminate\\Console\\Command',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'Config downloaded for ' . $device_name . ' with command: "show version" was successful',
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => $device_name,
                'device_id' => $deviceid,
                'events_ids' => null,
                'connection_category' => 'device',
                'connection_ids' => 'N;',
                'class' => 'Illuminate\\Console\\Command',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'Config downloaded for ' . $device_name . ' with command: "show clock" was successful',
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => $device_name,
                'device_id' => $deviceid,
                'events_ids' => null,
                'connection_category' => 'device',
                'connection_ids' => 'N;',
                'class' => 'Illuminate\\Console\\Command',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'End device download for ' . $device_name . ' ID:' . $deviceid,
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => $device_name,
                'device_id' => $deviceid,
                'events_ids' => null,
                'connection_category' => 'rconfig:download-device',
                'connection_ids' => 'i:;' . $deviceid,
                'class' => 'Illuminate\\Console\\Command',
                'function' => 'handle',
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ],
            [
                'log_name' => 'info',
                'description' => 'Start device download for ' . $device_name . ' ID:' . $deviceid,
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => $device_name,
                'device_id' => $deviceid,
                'events_ids' => null,
                'connection_category' => 'rconfig:download-device',
                'connection_ids' => 'i:;' . $deviceid,
                'class' => 'Illuminate\\Console\\Command',
                'function' => 'handle',
                'created_at' => '2022-04-18 15:55:38',
                'updated_at' => '2022-04-18 15:55:38',
            ],
            [
                'log_name' => 'info',
                'description' => 'CLI Download Started for rconfig:download-deviceIDs:' . $deviceid,
                'subject_id' => null,
                'subject_type' => null,
                'causer_id' => null,
                'causer_type' => null,
                'properties' => '[]',
                'batch_uuid' => null,
                'event_type' => 'connection',
                'device_name' => '',
                'device_id' => '',
                'events_ids' => null,
                'connection_category' => 'rconfig:download-device',
                'connection_ids' => 'a:1:{i:0;s:5:"' . $deviceid . ';}',
                'class' => 'App\\Console\\Commands\\rconfigDeviceDownload',
                'function' => 'handle',
                'created_at' => '2022-04-18 15:55:38',
                'updated_at' => '2022-04-18 15:55:38',
            ],
        ];
    }
} // END CLASS
