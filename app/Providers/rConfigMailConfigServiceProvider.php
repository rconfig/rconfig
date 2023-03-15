<?php

namespace App\Providers;

use App\Models\Setting;
use Config;
use Illuminate\Support\ServiceProvider;

class rConfigMailConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (\Schema::hasTable('settings')) {
            $settings = Setting::where('id', 1)->first();
            // dd($settings);

            if ($settings) { //checking if table is not empty
                // mail_password decrypted at the model
                $config = [
                    'driver' => $settings->mail_driver,
                    'host' => $settings->mail_host,
                    'port' => $settings->mail_port,
                    'from' => ['address' => $settings->mail_from_email, 'name' => 'rConfig Notification'],
                    'encryption' => $settings->mail_encryption,
                    'username' => $settings->mail_username,
                    'password' => $settings->mail_password,
                    'sendmail' => '/usr/sbin/sendmail -bs',
                    'pretend' => false,
                    'verify_peer' => false,
                ];
                Config::set('mail', $config);
            }
        }
    }

    public function register()
    {
    }
}
