<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MonitoredScheduledTaskLogItemsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task_id' => 444444,
            'monitored_scheduled_task_id' => 2,
            'type' => 'rconfig:download-device',
            'meta' => 'Task finished',
        ];
    }
}
