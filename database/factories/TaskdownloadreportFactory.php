<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskdownloadreportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $report_id = $this->faker->uuid;

        return [
            'report_id' => $report_id,
            'task_id' => $this->faker->numberBetween($min = 100000, $max = 999999),
            'task_name' => $this->faker->word,
            'task_desc' => $this->faker->sentence,
            'task_type' => $this->faker->word,
            'file_name' => $report_id.'.html',
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addSeconds(59),
            'duration' => $this->faker->randomDigit(),
        ];
    }
}
