<?php

namespace App\Services\Email;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Loads mail configuration from the settings table (not .env) and caches it
 * under the "mail_settings" key when the mail system is first resolved.
 */
class MailConfigService
{
    private bool $configured = false;

    public function configure(): void
    {
        if ($this->configured) {
            return;
        }

        $settings = Cache::remember('mail_settings', 1440, function () {
            return Setting::first([
                'mail_driver',
                'mail_host',
                'mail_port',
                'mail_from_email',
                'mail_encryption',
                'mail_username',
                'mail_password',
                'mail_verify_peer',
                'mail_auto_tls',
            ]);
        });

        if ($settings) {
            // Force the default mailer to smtp so the database-driven config is used
            Config::set('mail.default', 'smtp');

            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $settings->mail_host,
                'port' => $settings->mail_port,
                'encryption' => $settings->mail_encryption,
                'username' => $settings->mail_username,
                'password' => $settings->mail_password,
                'verify_peer' => (bool) ($settings->mail_verify_peer ?? false),
                'auto_tls' => (bool) ($settings->mail_auto_tls ?? false),
            ]);

            Config::set('mail.from', [
                'address' => $settings->mail_from_email,
                'name' => 'rConfig Notification',
            ]);
        }

        $this->configured = true;
    }
}
