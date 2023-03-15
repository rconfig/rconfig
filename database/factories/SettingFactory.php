<?php

namespace Database\Factories;

use Crypt;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'login_banner' => 'Authorization message - You must be an authorized user to login and use this system.',
            'timezone' => 'Europe/Dublin',
            'mail_host' => \Config::get('MAIL_HOST', 'smtp.mailtrap.io'),
            'mail_port' => \Config::get('MAIL_PORT', 2525),
            'mail_from_email' => \Config::get('MAIL_FROM_ADDRESS', 'admin_dev@rconfig.com'),
            'mail_to_email' => $this->faker->email,
            'mail_authcheck' => \Config::get('MAIL_ENCRYPTION'),
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => Crypt::encrypt(env('MAIL_PASSWORD')),
            'mail_driver' => \Config::get('MAIL_DRIVER', 'smtp'),
            'mail_encryption' => \Config::get('MAIL_ENCRYPTION', 'tls'),
            'defaultDeviceUsername' => $this->faker->userName,
            'defaultDevicePassword' => $this->faker->password,
            'defaultEnablePassword' => $this->faker->password,
            'passwordEncryption' => $this->faker->boolean,
            'deviceDebugging' => $this->faker->boolean,
            'phpDebugging' => $this->faker->boolean,
        ];
    }
}
