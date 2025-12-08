<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'TmpUser' . $this->faker->name(),
            'username' => $this->faker->username(),
            'email' => $this->faker->numberBetween(1000, 9999999) . '@rconfig.com',
            'email_verified_at' => now(),
            'password' => Hash::make($this->faker->name()), // password
            'get_notifications' => true,
            'is_socialite_approved' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
