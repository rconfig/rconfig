<?php

namespace Database\Seeders\demodata;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTaskSeeder extends Seeder
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

        Task::insert([
            'id' => 618485,
            'task_name' => 'Download Routers',
            'task_desc' => 'Download Routers Category once a week',
            'task_command' => 'rconfig:download-category',
            'task_categories' => 1,
            'task_devices' => 0,
            'task_tags' => 0,
            'task_cron' => '0 0 * * 0 ',
            'task_email_notify' => 1,
            'download_report_notify' => 1,
            'verbose_download_report_notify' => 1,
            'is_system' => 0,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
        Task::insert([
            'id' => 836229,
            'task_name' => 'Download core devices',
            'task_desc' => 'Download core devices configs once per day',
            'task_command' => 'rconfig:download-device',
            'task_categories' => 0,
            'task_devices' => 1,
            'task_tags' => 0,
            'task_cron' => '0 0 1 1 * ',
            'task_email_notify' => 1,
            'download_report_notify' => 1,
            'verbose_download_report_notify' => 1,
            'is_system' => 0,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);

        DB::table('device_task')->insert([
            'device_id' => 50001,
            'task_id' => 836229,
        ]);
        DB::table('device_task')->insert([
            'device_id' => 500010,
            'task_id' => 836229,
        ]);
        DB::table('device_task')->insert([
            'device_id' => 500011,
            'task_id' => 836229,
        ]);
        DB::table('device_task')->insert([
            'device_id' => 500012,
            'task_id' => 836229,
        ]);
        DB::table('category_task')->insert([
            'category_id' => 1,
            'task_id' => 618485,
        ]);
    }
}
