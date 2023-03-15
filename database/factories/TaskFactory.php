<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'task_name' => $this->faker->firstNameMale . $this->faker->numberBetween($min = 100000, $max = 999999),
            'task_desc' => $this->faker->sentence,
            'task_command' => 'rconfig:download-device',
            'task_categories' => $this->faker->boolean,
            'task_devices' => $this->faker->boolean,
            'task_tags' => $this->faker->boolean,
            'task_email_notify' => $this->faker->boolean,
            'task_cron' => '0 0 * * 0 ',
            'download_report_notify' => $this->faker->boolean,
            'verbose_download_report_notify' => $this->faker->boolean,
            'is_system' => $this->faker->boolean,
        ];
    }
}
