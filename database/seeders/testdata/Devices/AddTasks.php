<?php

namespace Database\Seeders\testdata\Devices;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddTasks extends Seeder
{
    public static function run(): void
    {
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
