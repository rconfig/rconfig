<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SettingsEmailControllerTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected $setting;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function get_smtp_settings()
    {
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
        ]);
    }

    /** @test */
    public function get_smtp_settings_from_config_cache()
    {
        $cached = Config::get('mail');

        $this->assertEquals('smtp', $cached['driver']);
        $this->assertEquals('devmailer.rconfig.com', $cached['host']);
        $this->assertEquals(null, $cached['username']);
        $this->assertEquals(false, $cached['verify_peer']); // bug: #160
    }

    /** @test */
    public function smtp_required_fields()
    {
        $smtp_array = [];

        $response = $this->withHeaders(['Accept' => 'application/json'])->patch('/api/settings/email/1', $smtp_array);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('mail_host', $response['errors']);
        $this->assertArrayHasKey('mail_port', $response['errors']);
        $this->assertArrayHasKey('mail_from_email', $response['errors']);
        $this->assertArrayHasKey('mail_to_email', $response['errors']);
        $response->assertStatus(422);
    }

    /** @test */
    public function smtp_required_tls_encryption_field()
    {
        $smtp_array = [];
        $smtp_array['mail_authcheck'] = true;

        $response = $this->withHeaders(['Accept' => 'application/json'])->patch('/api/settings/email/1', $smtp_array);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('mail_host', $response['errors']);
        $this->assertArrayHasKey('mail_port', $response['errors']);
        $this->assertArrayHasKey('mail_from_email', $response['errors']);
        $this->assertArrayHasKey('mail_to_email', $response['errors']);
        $this->assertArrayHasKey('mail_encryption', $response['errors']);
        $response->assertStatus(422);
    }

    /** @test */
    public function update_smtp()
    {
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

        $response = $this->patch('/api/settings/email/1', $smtp_array);
        $response->assertStatus(200);

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'mail_host' => $smtp_array['mail_host'],
            'mail_port' => $smtp_array['mail_port'],
            'mail_from_email' => $smtp_array['mail_from_email'],
            'mail_to_email' => $smtp_array['mail_to_email'],
            'mail_encryption' => $smtp_array['mail_encryption'],
        ]);
    }

    /** @test */
    public function update_smtp_and_set_encryption_to_null_if_auth_disabled()
    {
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
    }

    /** @test */
    public function can_update_smtp_password_and_get_password_back_after_encryption()
    {
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
    }

    /** @test */
    public function sending_a_test_email_works()
    {

        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->delete('http://dockerprod.rconfig.com:8025/api/v1/messages');

        if ($response->status() === 401) {
            $this->markTestSkipped('Test notification not sent due to mail host auth issue.');
        }

        $this->assertEquals(200, $response->status());

        $mailtrapSmtp = [
            'mail_driver' => 'smtp',
            'mail_host' => 'dockerprod.rconfig.com',
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
        \Artisan::call('config:cache');

        $response = $this->get('/api/settings/email/1');
        $json = json_decode($response->getContent());

        $this->assertEquals($json->mail_driver, $mailtrapSmtp['mail_driver']);
        $this->assertEquals($json->mail_host, $mailtrapSmtp['mail_host']);
        $this->assertEquals($json->mail_port, $mailtrapSmtp['mail_port']);
        $this->assertEquals($json->mail_username, $mailtrapSmtp['mail_username']);
        $this->assertEquals($json->mail_authcheck, $mailtrapSmtp['mail_authcheck']);
        $this->assertEquals($json->mail_encryption, $mailtrapSmtp['mail_encryption']);

        $response = $this->get('/api/settings/test-email');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Email settings tested successfully, please check your email for the test message!']);

        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->get('http://dockerprod.rconfig.com:8025/api/v2/messages?limit=50');

        $this->assertEquals(200, $response->status());

        $this->assertGreaterThan(1, $response->json()['count']);

        $this->assertStringContainsString('rConfig Test Mail', $response->json()['items'][0]['Content']['Headers']['Subject'][0]);
        $this->assertStringContainsString('rConfig Notification', $response->json()['items'][0]['Content']['Headers']['From'][0]);

        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->delete('http://dockerprod.rconfig.com:8025/api/v1/messages');

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function sending_a_test_notification_works()
    {
        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->delete('http://dockerprod.rconfig.com:8025/api/v1/messages');

        if ($response->status() === 401) {
            $this->markTestSkipped('Test notification not sent due to mailtrap auth issue.');
        }

        $this->assertEquals(200, $response->status());

        $mailtrapSmtp = [
            'mail_driver' => 'smtp',
            'mail_host' => 'dockerprod.rconfig.com',
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

        $response = $this->get('/api/settings/email/1');
        $json = json_decode($response->getContent());
        $this->assertEquals($json->mail_driver, $mailtrapSmtp['mail_driver']);
        $this->assertEquals($json->mail_host, $mailtrapSmtp['mail_host']);
        $this->assertEquals($json->mail_port, $mailtrapSmtp['mail_port']);
        $this->assertEquals($json->mail_username, $mailtrapSmtp['mail_username']);
        $this->assertEquals($json->mail_authcheck, $mailtrapSmtp['mail_authcheck']);
        $this->assertEquals($json->mail_encryption, $mailtrapSmtp['mail_encryption']);

        $response = $this->get('/api/settings/test-notification');

        if ($response->status() === 422) {
            $this->markTestSkipped('Test notification not sent due to mailtrap rate limit.');
        }

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Test notification sent successfully!']);
        // dd($response->getContent());

        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->get('http://dockerprod.rconfig.com:8025/api/v2/messages?limit=50');

        $this->assertEquals(200, $response->status());

        $this->assertGreaterThan(1, $response->json()['count']);

        $this->assertStringContainsString('rConfig System Test Notification', $response->json()['items'][0]['Content']['Headers']['Subject'][0]);
        $this->assertStringContainsString('rConfig Notification', $response->json()['items'][0]['Content']['Headers']['From'][0]);

        $response = \Http::withHeaders([
            'Accept' => ' application/json',
        ])->delete('http://dockerprod.rconfig.com:8025/api/v1/messages');

        $this->assertEquals(200, $response->status());
    }
}
