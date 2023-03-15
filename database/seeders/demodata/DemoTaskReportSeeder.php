<?php

namespace Database\Seeders\demodata;

use App\Models\Taskdownloadreport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DemoTaskReportSeeder extends Seeder
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

        $d = 1;
        $reports = [];

        for ($d = 1; $d < 26; $d++) {
            $uuid = $this->faker->uuid();
            $datetime = $this->faker->dateTimeBetween('-'.$d.' weeks', '-'.$d - 1 .' weeks');

            $task = [];
            if ($d % 2 == 0) {
                $task = [
                    'task_id' => 836229,
                    'task_name' => 'Download core devices',
                    'task_desc' => 'Download core devices configs once per day',
                    'file_name' => '836229.html',
                ];
            } else {
                $task = [
                    'task_id' => 618485,
                    'task_name' => 'Download Routers',
                    'task_desc' => 'Download Routers Category once a week',
                    'file_name' => '618485.html',
                ];
            }

            $reports[$d]['id'] = $d;
            $reports[$d]['report_id'] = $uuid;
            $reports[$d]['task_id'] = $task['task_id'];
            $reports[$d]['task_name'] = $task['task_name'];
            $reports[$d]['task_desc'] = $task['task_desc'];
            $reports[$d]['file_name'] = $task['file_name'];
            $reports[$d]['start_time'] = $datetime;
            $reports[$d]['end_time'] = $datetime;
            $reports[$d]['created_at'] = $datetime;
            $reports[$d]['updated_at'] = $datetime;
        }
        foreach ($reports as $report) {
            Taskdownloadreport::create(
                [
                    'id' => $report['id'],
                    'report_id' => $report['report_id'],
                    'task_id' => $report['task_id'],
                    'task_name' => $report['task_name'],
                    'task_desc' => $report['task_desc'],
                    'task_type' => 'Task Download Report',
                    'file_name' => $report['file_name'],
                    'start_time' => $report['start_time'],
                    'end_time' => $report['end_time'],
                    'duration' => 57,
                    'created_at' => $report['created_at'],
                    'updated_at' => $report['updated_at'],
                ]
            );
            if ($report['task_id'] === 836229) {
                File::copy(rconfig_appdir_path().'/database/seeders/demodata/demoreports/836229.html', report_path().$report['report_id'].'.html');
            }
            if ($report['task_id'] === 618485) {
                File::copy(rconfig_appdir_path().'/database/seeders/demodata/demoreports/618485.html', report_path().$report['report_id'].'.html');
            }
        }

        // if (!File::exists(report_path() . '836229.html')) {
        //     File::copy(rconfig_appdir_path() . '/database/seeders/demodata/demoreports/836229.html', report_path() . '836229.html');
        // }
    }
}
