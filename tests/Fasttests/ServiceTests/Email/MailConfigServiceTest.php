<?php

use App\Models\Setting;
use App\Services\Email\MailConfigService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->beginTransaction();

    // Critical: clear cache and resolve a fresh service instance
    Cache::forget('mail_settings');
    $this->service = app()->make(MailConfigService::class);
});

afterEach(function () {
    // Critical: clear cache and reset the singleton between tests
    Cache::forget('mail_settings');
    app()->forgetInstance(MailConfigService::class);

    $this->rollBackTransaction();

});

test('it configures mail settings from database', function () {
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

    expect(Config::get('mail.mailers.smtp.transport'))->toEqual('smtp');
    expect(Config::get('mail.mailers.smtp.host'))->toEqual('test.smtp.com');
    expect(Config::get('mail.mailers.smtp.port'))->toEqual(587);
    expect(Config::get('mail.mailers.smtp.encryption'))->toEqual('tls');
    expect(Config::get('mail.mailers.smtp.username'))->toEqual('testuser');
    expect(Config::get('mail.mailers.smtp.password'))->toEqual('testpass');
    expect(Config::get('mail.mailers.smtp.verify_peer'))->toBeTrue();
    expect(Config::get('mail.mailers.smtp.auto_tls'))->toBeTrue();
    expect(Config::get('mail.from.address'))->toEqual('test@example.com');
    expect(Config::get('mail.from.name'))->toEqual('rConfig Notification');
});

test('it caches mail settings', function () {
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

    expect(Cache::has('mail_settings'))->toBeTrue();

    $cached = Cache::get('mail_settings');
    expect($cached->mail_driver)->toEqual('smtp');
    expect($cached->mail_host)->toEqual('cached.smtp.com');
    expect((int) $cached->mail_verify_peer)->toEqual(0);
    expect((int) $cached->mail_auto_tls)->toEqual(1);
});

test('it uses cached settings on subsequent calls', function () {
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
    expect(Config::get('mail.mailers.smtp.host'))->toEqual($originalHost);
});

test('it only configures once per instance', function () {
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
    $this->service->configure();

    // Second call is a no-op
    // Config should not be overwritten because the instance was already configured
    expect(Config::get('mail.mailers.smtp.host'))->toEqual('manually-changed.com');
});

test('it handles null values gracefully', function () {
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

    expect(Config::get('mail.mailers.smtp.transport'))->toEqual('smtp');
    expect(Config::get('mail.mailers.smtp.host'))->toEqual('test.smtp.com');
    expect(Config::get('mail.mailers.smtp.encryption'))->toBeNull();
    expect(Config::get('mail.mailers.smtp.verify_peer'))->toBeFalse();
    expect(Config::get('mail.mailers.smtp.auto_tls'))->toBeFalse();
});
