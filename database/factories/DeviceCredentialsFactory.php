<?php

namespace Database\Factories;

use App\Models\DeviceCredentials;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceCredentials>
 */
class DeviceCredentialsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cred_name' => $this->faker->name,
            'cred_description' => $this->faker->text,
            'cred_username' => $this->faker->userName,
            'cred_password' => $this->faker->password,
            'cred_enable_password' => $this->faker->password,
        ];
    }
}
