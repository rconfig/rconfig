<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_name' => 'device'.$this->faker->randomNumber($nbDigits = 9),
            'device_ip' => $this->faker->ipv4,
            'device_default_creds_on' => 0,
            'device_username' => $this->faker->userName,
            'device_password' => $this->faker->password,
            'device_enable_password' => $this->faker->password,
            'device_cred_id' => 0,
            'device_main_prompt' => 'anEnablePrompt#',
            'device_enable_prompt' => 'anEnablePrompt>',
            'device_category_id' => 9999,
            'device_template' => $this->faker->numberBetween(10, 100),
            'device_model' => $this->faker->word,
            'device_version' => $this->faker->randomDigit,
            'device_added_by' => $this->faker->userName,
            'created_at' => $this->faker->dateTimeThisDecade($max = 'now', $timezone = null),
            'updated_at' => $this->faker->dateTimeThisDecade($max = 'now', $timezone = null),
            'last_seen' => '2020-05-14 11:53:01',
            'status' => 1,
        ];
    }
}
