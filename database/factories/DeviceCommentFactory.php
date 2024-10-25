<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceComment>
 */
class DeviceCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->randomNumber(),
            'user_id' => $this->faker->randomNumber(),
            'comment' => $this->faker->text(),
            'is_open' => $this->faker->boolean(),
        ];
    }
}
