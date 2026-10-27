<?php

use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailHealthCheckNotification;
use App\Services\Notifications\HealthCheckNotifier;
use App\Services\Notifications\Shared\HealthCheckAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->beginTransaction();

    Notification::fake();

    foreach (['system.health_check_failed', 'system.health_check_recovered'] as $type) {
        DB::table('notifications_defaults')->updateOrInsert(
            ['notification_type' => $type],
            ['category' => 'system', 'default_db' => 1, 'default_mail' => 1, 'description' => $type, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    $this->recipient = User::factory()->create(['get_notifications' => true]);
});

afterEach(function () {
    $this->rollBackTransaction();
});

function healthFailedAlert(): HealthCheckAlert
{
    return new HealthCheckAlert(false, [
        ['name' => 'Redis', 'label' => 'Redis', 'status' => 'failed', 'summary' => '', 'message' => 'Could not connect to Redis'],
        ['name' => 'Horizon', 'label' => 'Horizon', 'status' => 'failed', 'summary' => '', 'message' => 'Horizon is not running'],
    ]);
}

test('a failed alert sends one in-app and one mail notification', function () {
    expect(app(HealthCheckNotifier::class)->notify(healthFailedAlert()))->toBeTrue();

    Notification::assertSentToTimes($this->recipient, DBNotification::class, 1);
    Notification::assertSentToTimes($this->recipient, MailHealthCheckNotification::class, 1);
    Notification::assertSentTo($this->recipient, DBNotification::class, function (DBNotification $notification): bool {
        $data = $notification->toArray($this->recipient);

        return $data['title'] === 'Health Check Failed'
            && $data['severity'] === 'error'
            && $data['category'] === 'system'
            && str_contains($data['description'], 'Redis, Horizon');
    });
});

test('the failed mail is an error listing each check with a link to the dashboard', function () {
    $mail = (new MailHealthCheckNotification(healthFailedAlert()))->toMail($this->recipient);

    expect($mail->subject)->toBe('rConfig Health Check Failed')
        ->and($mail->level)->toBe('error')
        ->and($mail->introLines)->toContain('• Redis: Could not connect to Redis', '• Horizon: Horizon is not running')
        ->and($mail->actionUrl)->toEndWith('/dashboard');
});

test('a recovered alert uses the recovered type and is not an error', function () {
    $alert = new HealthCheckAlert(true, [
        ['name' => 'Redis', 'label' => 'Redis', 'status' => 'ok', 'summary' => 'Ok', 'message' => ''],
    ]);

    app(HealthCheckNotifier::class)->notify($alert);

    Notification::assertSentTo($this->recipient, DBNotification::class, function (DBNotification $notification): bool {
        $data = $notification->toArray($this->recipient);

        return $data['title'] === 'Health Check Recovered' && $data['severity'] === 'info';
    });

    $mail = (new MailHealthCheckNotification($alert))->toMail($this->recipient);

    expect($mail->subject)->toBe('rConfig Health Check Recovered')
        ->and($mail->level)->not->toBe('error')
        ->and($mail->introLines)->toContain('• Redis: Ok');
});

test('mail respects a user opt-out', function () {
    DB::table('notifications_user_preferences')->insert([
        'user_id' => $this->recipient->id,
        'notification_type' => 'system.health_check_failed',
        'channel' => 'mail',
        'enabled' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(HealthCheckNotifier::class)->notify(healthFailedAlert());

    Notification::assertSentToTimes($this->recipient, DBNotification::class, 1);
    Notification::assertNotSentTo($this->recipient, MailHealthCheckNotification::class);
});

test('a send failure does not escape the notifier', function () {
    Notification::shouldReceive('send')->andThrow(new RuntimeException('mail server on fire'));

    expect(app(HealthCheckNotifier::class)->notify(healthFailedAlert()))->toBeTrue();
});
