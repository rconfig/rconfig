<?php

namespace Database\Factories;

use App\Models\DeviceComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceComment>
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
