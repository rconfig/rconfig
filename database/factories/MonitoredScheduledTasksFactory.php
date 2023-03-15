<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MonitoredScheduledTasksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task_id' => 123123,
            'name' => 'DevTask1',
            'type' => 'rconfig:download-device',
            'cron_expression' => '0 0 * * 0',
            'timezone' => null,
            'ping_url' => null,
            'last_started_at' => '2022-06-27 12:00:08',
            'last_finished_at' => '2022-06-27 12:00:08',
            'last_failed_at' => null,
            'last_skipped_at' => null,
            'created_at' => '2022-06-22 22:00:02',
            'updated_at' => '2022-06-27 12:00:08',
        ];
    }
}
