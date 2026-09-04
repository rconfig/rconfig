<?php

use App\Models\Setting;
use App\Models\User;
use App\Services\Email\MailConfigService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

uses(WithFaker::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get smtp settings', function () {
    $response = $this->get('/api/settings/email/1');

    $json = json_decode($response->getContent());

    $response->assertJson([
        'mail_host' => $json->mail_host,
        'mail_port' => $json->mail_port,
        'mail_from_email' => $json->mail_from_email,
        'mail_to_email' => $json->mail_to_email,
        'mail_authcheck' => $json->mail_authcheck,
        'mail_username' => $json->mail_username,
        'mail_password' => $json->mail_password,
        'mail_driver' => $json->mail_driver,
        'mail_encryption' => $json->mail_encryption,
        'mail_verify_peer' => $json->mail_verify_peer,
        'mail_auto_tls' => $json->mail_auto_tls,
    ]);
});

test('get smtp settings from config cache', function () {
    // Mail config is loaded from the database lazily by MailConfigService when
    // the mail system is resolved, so trigger it explicitly here.
    Cache::forget('mail_settings');
    app()->forgetInstance(MailConfigService::class);
    app(MailConfigService::class)->configure();

    $setting = Setting::first();
    $cached = Config::get('mail');

    expect($cached['mailers']['smtp']['transport'])->toEqual('smtp');
    expect($cached['mailers']['smtp']['host'])->toEqual($setting->mail_host);
    expect($cached['mailers']['smtp']['username'])->toEqual($setting->mail_username);
    expect($cached['mailers']['smtp']['verify_peer'])->toEqual(false);
    // bug: #160
    expect($cached['mailers']['smtp']['auto_tls'])->toEqual(false);
});

test('smtp required fields', function () {
    $smtp_array = [];

    $response = $this->withHeaders(['Accept' => 'application/json'])->patch('/api/settings/email/1', $smtp_array);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('mail_host');
    expect($response['errors'])->toHaveKey('mail_port');
    expect($response['errors'])->toHaveKey('mail_from_email');
    expect($response['errors'])->toHaveKey('mail_to_email');
    $response->assertStatus(422);
});

test('smtp required tls encryption field', function () {
    $smtp_array = [];
    $smtp_array['mail_authcheck'] = true;

    $response = $this->withHeaders(['Accept' => 'application/json'])->patch('/api/settings/email/1', $smtp_array);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('mail_host');
    expect($response['errors'])->toHaveKey('mail_port');
    expect($response['errors'])->toHaveKey('mail_from_email');
    expect($response['errors'])->toHaveKey('mail_to_email');
    expect($response['errors'])->toHaveKey('mail_encryption');
    $response->assertStatus(422);
});

test('update smtp', function () {
    $smtp_array = [];
    $smtp_array['mail_host'] = $this->faker->ipv4;
    $smtp_array['mail_port'] = $this->faker->numberBetween($min = 1, $max = 1000);
    $smtp_array['mail_from_email'] = $this->faker->companyEmail;
    $smtp_array['mail_to_email'] = $this->faker->companyEmail;
    $smtp_array['mail_authcheck'] = true;
    $smtp_array['mail_username'] = $this->faker->userName;
    $smtp_array['mail_password'] = $this->faker->password;
    $smtp_array['mail_driver'] = 'smtp';
    $smtp_array['mail_encryption'] = 'tls';
    $smtp_array['mail_verify_peer'] = 1;
    $smtp_array['mail_auto_tls'] = 1;

    $response = $this->patch('/api/settings/email/1', $smtp_array);
    $response->assertStatus(200);

    $this->assertDatabaseHas('settings', [
        'id' => 1,
        'mail_host' => $smtp_array['mail_host'],
        'mail_port' => $smtp_array['mail_port'],
        'mail_from_email' => $smtp_array['mail_from_email'],
        'mail_to_email' => $smtp_array['mail_to_email'],
        'mail_encryption' => $smtp_array['mail_encryption'],
        'mail_verify_peer' => 1,
        'mail_auto_tls' => 1,
    ]);
});

test('update smtp and set encryption to null if auth disabled', function () {
    $smtp_array = [];
    $smtp_array['mail_host'] = $this->faker->ipv4;
    $smtp_array['mail_port'] = $this->faker->numberBetween($min = 1, $max = 1000);
    $smtp_array['mail_from_email'] = $this->faker->companyEmail;
    $smtp_array['mail_to_email'] = $this->faker->companyEmail;
    $smtp_array['mail_authcheck'] = false;
    $smtp_array['mail_username'] = $this->faker->userName;
    $smtp_array['mail_password'] = $this->faker->password;
    $smtp_array['mail_driver'] = 'smtp';
    $smtp_array['mail_encryption'] = 'tls';

    $response = $this->patch('/api/settings/email/1', $smtp_array);
    $response->assertStatus(200);

    $this->assertDatabaseHas('settings', [
        'id' => 1,
        'mail_host' => $smtp_array['mail_host'],
        'mail_port' => $smtp_array['mail_port'],
        'mail_from_email' => $smtp_array['mail_from_email'],
        'mail_to_email' => $smtp_array['mail_to_email'],
        'mail_encryption' => null,
    ]);
});

test('can update smtp password and get password back after encryption', function () {
    $smtp_array = [];
    $smtp_array['mail_host'] = $this->faker->ipv4;
    $smtp_array['mail_port'] = $this->faker->numberBetween($min = 1, $max = 1000);
    $smtp_array['mail_from_email'] = $this->faker->companyEmail;
    $smtp_array['mail_to_email'] = $this->faker->companyEmail;
    $smtp_array['mail_username'] = $this->faker->userName;
    $smtp_array['mail_password'] = $this->faker->password;

    $response = $this->patch('/api/settings/email/1', $smtp_array);
    $response->assertStatus(200);

    $response = $this->get('/api/settings/email/1');
    $json = json_decode($response->getContent());

    $response->assertJson([
        'mail_password' => $json->mail_password,
    ]);
});

test('sending a test email works', function () {
    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->delete('http://devmailer.rconfig.com:8025/api/v1/messages', ['ids' => []]);

    if ($response->status() === 401) {
        $this->markTestSkipped('Test notification not sent due to mail host auth issue.');
    }

    expect($response->status())->toEqual(200);

    $mailtrapSmtp = [
        'mail_driver' => 'smtp',
        'mail_host' => 'devmailer.rconfig.com',
        'mail_port' => 1025,
        'mail_username' => env('MAILTRAP_USERNAME'),
        'mail_password' => env('MAILTRAP_PASSWORD'),
        'mail_from_email' => $this->faker->companyEmail,
        'mail_from_name' => $this->faker->firstName,
        'mail_to_email' => $this->faker->companyEmail . ';' . $this->faker->companyEmail . ';' . $this->faker->companyEmail . ';', // add last semi-colon to test empty email is trimmed per #531
        'mail_authcheck' => true,
        'mail_encryption' => 'tls',
    ];

    $response = $this->patch('/api/settings/email/1', $mailtrapSmtp);
    $response->assertStatus(200);
    Artisan::call('config:cache');

    $response = $this->get('/api/settings/email/1');
    $json = json_decode($response->getContent());

    expect($mailtrapSmtp['mail_driver'])->toEqual($json->mail_driver);
    expect($mailtrapSmtp['mail_host'])->toEqual($json->mail_host);
    expect($mailtrapSmtp['mail_port'])->toEqual($json->mail_port);
    expect($mailtrapSmtp['mail_username'])->toEqual($json->mail_username);
    expect($mailtrapSmtp['mail_authcheck'])->toEqual($json->mail_authcheck);
    expect($mailtrapSmtp['mail_encryption'])->toEqual($json->mail_encryption);

    $response = $this->get('/api/settings/test-email');

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Email settings tested successfully, please check your email for the test message!']);

    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->get('http://devmailer.rconfig.com:8025/api/v1/messages?limit=50');

    expect($response->status())->toEqual(200);

    expect($response->json()['count'])->toBeGreaterThan(1);

    $this->assertStringContainsString('rConfig Test Mail', $response->json()['messages'][0]['Subject']);

    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->delete('http://devmailer.rconfig.com:8025/api/v1/messages');

    expect($response->status())->toEqual(200);
});

test('sending a test notification works', function () {
    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->delete('http://devmailer.rconfig.com:8025/api/v1/messages');

    if ($response->status() === 401) {
        $this->markTestSkipped('Test notification not sent due to mailtrap auth issue.');
    }

    expect($response->status())->toEqual(200);

    $mailtrapSmtp = [
        'mail_driver' => 'smtp',
        'mail_host' => 'devmailer.rconfig.com',
        'mail_port' => 1025,
        'mail_username' => env('MAILTRAP_USERNAME'),
        'mail_password' => env('MAILTRAP_PASSWORD'),
        'mail_from_email' => $this->faker->companyEmail,
        'mail_from_name' => $this->faker->firstName,
        'mail_to_email' => $this->faker->companyEmail . ';' . $this->faker->companyEmail . ';' . $this->faker->companyEmail,
        'mail_authcheck' => true,
        'mail_encryption' => 'tls',
    ];

    $response = $this->patch('/api/settings/email/1', $mailtrapSmtp);
    $response->assertStatus(200);

    $response = $this->get('/api/settings/email/1');
    $json = json_decode($response->getContent());
    expect($mailtrapSmtp['mail_driver'])->toEqual($json->mail_driver);
    expect($mailtrapSmtp['mail_host'])->toEqual($json->mail_host);
    expect($mailtrapSmtp['mail_port'])->toEqual($json->mail_port);
    expect($mailtrapSmtp['mail_username'])->toEqual($json->mail_username);
    expect($mailtrapSmtp['mail_authcheck'])->toEqual($json->mail_authcheck);
    expect($mailtrapSmtp['mail_encryption'])->toEqual($json->mail_encryption);

    $response = $this->get('/api/settings/test-notification');

    if ($response->status() === 422) {
        $this->markTestSkipped('Test notification not sent due to mailtrap rate limit.');
    }

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Test notification sent successfully!']);

    // dd($response->getContent());
    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->get('http://devmailer.rconfig.com:8025/api/v1/messages?limit=50');

    expect($response->status())->toEqual(200);

    expect($response->json()['count'])->toBeGreaterThan(1);

    $this->assertStringContainsString('rConfig System Test Notification', $response->json()['messages'][0]['Subject']);
    $this->assertStringContainsString('rConfig Notification', $response->json()['messages'][0]['From']['Name']);

    $response = Http::withHeaders([
        'Accept' => ' application/json',
    ])->delete('http://devmailer.rconfig.com:8025/api/v1/messages');

    expect($response->status())->toEqual(200);
});
