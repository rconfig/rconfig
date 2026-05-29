<?php

namespace Tests\Unit\Services\Email;

use App\Models\Setting;
use App\Services\Email\MailConfigService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MailConfigServiceTest extends TestCase
{
    protected MailConfigService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        // Critical: clear cache and resolve a fresh service instance
        Cache::forget('mail_settings');
        $this->service = app()->make(MailConfigService::class);
    }

    protected function tearDown(): void
    {
        // Critical: clear cache and reset the singleton between tests
        Cache::forget('mail_settings');
        app()->forgetInstance(MailConfigService::class);

        $this->rollBackTransaction();

        parent::tearDown();
    }

    public function test_it_configures_mail_settings_from_database(): void
    {
        $setting = Setting::first();
        $setting->update([
            'mail_driver' => 'smtp',
            'mail_host' => 'test.smtp.com',
            'mail_port' => 587,
            'mail_from_email' => 'test@example.com',
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_verify_peer' => 1,
            'mail_auto_tls' => 1,
        ]);

        Cache::forget('mail_settings');

        $this->service->configure();

        $this->assertEquals('smtp', Config::get('mail.mailers.smtp.transport'));
        $this->assertEquals('test.smtp.com', Config::get('mail.mailers.smtp.host'));
        $this->assertEquals(587, Config::get('mail.mailers.smtp.port'));
        $this->assertEquals('tls', Config::get('mail.mailers.smtp.encryption'));
        $this->assertEquals('testuser', Config::get('mail.mailers.smtp.username'));
        $this->assertEquals('testpass', Config::get('mail.mailers.smtp.password'));
        $this->assertTrue(Config::get('mail.mailers.smtp.verify_peer'));
        $this->assertTrue(Config::get('mail.mailers.smtp.auto_tls'));
        $this->assertEquals('test@example.com', Config::get('mail.from.address'));
        $this->assertEquals('rConfig Notification', Config::get('mail.from.name'));
    }

    public function test_it_caches_mail_settings(): void
    {
        $setting = Setting::first();
        $setting->update([
            'mail_driver' => 'smtp',
            'mail_host' => 'cached.smtp.com',
            'mail_port' => 587,
            'mail_from_email' => 'cached@example.com',
            'mail_encryption' => 'tls',
            'mail_username' => 'cacheduser',
            'mail_password' => 'cachedpass',
            'mail_verify_peer' => 0,
            'mail_auto_tls' => 1,
        ]);

        Cache::forget('mail_settings');

        $this->service->configure();

        $this->assertTrue(Cache::has('mail_settings'));

        $cached = Cache::get('mail_settings');
        $this->assertEquals('smtp', $cached->mail_driver);
        $this->assertEquals('cached.smtp.com', $cached->mail_host);
        $this->assertEquals(0, (int) $cached->mail_verify_peer);
        $this->assertEquals(1, (int) $cached->mail_auto_tls);
    }

    public function test_it_uses_cached_settings_on_subsequent_calls(): void
    {
        $setting = Setting::first();
        $originalHost = 'original.smtp.com';
        $setting->update([
            'mail_driver' => 'smtp',
            'mail_host' => $originalHost,
            'mail_port' => 587,
            'mail_from_email' => 'test@example.com',
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_verify_peer' => 0,
            'mail_auto_tls' => 0,
        ]);

        Cache::forget('mail_settings');

        $this->service->configure();

        // Change DB but do not clear cache
        $setting->update(['mail_host' => 'changed.smtp.com']);

        app()->forgetInstance(MailConfigService::class);
        $newService = app()->make(MailConfigService::class);
        $newService->configure();

        // Should still use the cached value
        $this->assertEquals($originalHost, Config::get('mail.mailers.smtp.host'));
    }

    public function test_it_only_configures_once_per_instance(): void
    {
        $setting = Setting::first();
        $setting->update([
            'mail_driver' => 'smtp',
            'mail_host' => 'original.smtp.com',
            'mail_port' => 587,
            'mail_from_email' => 'test@example.com',
            'mail_encryption' => 'tls',
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_verify_peer' => 0,
            'mail_auto_tls' => 0,
        ]);

        Cache::forget('mail_settings');

        $this->service->configure();
        Config::set('mail.mailers.smtp.host', 'manually-changed.com');
        $this->service->configure(); // Second call is a no-op

        // Config should not be overwritten because the instance was already configured
        $this->assertEquals('manually-changed.com', Config::get('mail.mailers.smtp.host'));
    }

    public function test_it_handles_null_values_gracefully(): void
    {
        $setting = Setting::first();
        $setting->update([
            'mail_driver' => 'smtp',
            'mail_host' => 'test.smtp.com',
            'mail_port' => 25,
            'mail_from_email' => 'test@example.com',
            'mail_encryption' => null,
            'mail_username' => null,
            'mail_password' => null,
            'mail_verify_peer' => null,
            'mail_auto_tls' => null,
        ]);

        Cache::forget('mail_settings');

        $this->service->configure();

        $this->assertEquals('smtp', Config::get('mail.mailers.smtp.transport'));
        $this->assertEquals('test.smtp.com', Config::get('mail.mailers.smtp.host'));
        $this->assertNull(Config::get('mail.mailers.smtp.encryption'));
        $this->assertFalse(Config::get('mail.mailers.smtp.verify_peer'));
        $this->assertFalse(Config::get('mail.mailers.smtp.auto_tls'));
    }
}
