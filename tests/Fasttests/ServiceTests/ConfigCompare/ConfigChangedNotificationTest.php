<?php

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Jobs\ConfigChangedNotificationJob;
use App\Models\Command;
use App\Models\Config;
use App\Models\User;
use App\Notifications\MailConfigChangedNotification;
use App\Services\ConfigHistory\ConfigHistoryManager;
use App\Services\Templates\CompareExclusionTemplateService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->beginTransaction();

    $this->command = 'show run notif test';

    (new CompareExclusionTemplateService)->installDefaultTemplate();
    Command::firstOrCreate(['command' => $this->command]);

    $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('notif_', true) . '/');
    File::makeDirectory($this->workDir, 0777, true, true);
});

afterEach(function () {
    $this->rollBackTransaction();
    File::deleteDirectory($this->workDir);
    File::delete(File::glob(tmp_dir() . '/*.txt'));
});

function notificationWriteFile(string $workDir, string $name, string $content): string
{
    $path = $workDir . $name;
    File::put($path, $content);

    return $path;
}

function notificationMakeConfig(string $command, string $filePath, ?int $version, int $latest): Config
{
    return Config::create([
        'device_id' => 778899,
        'device_name' => 'notif-router',
        'device_category' => 'Routers',
        'command' => $command,
        'type' => 'device_download',
        'download_status' => 1,
        'config_location' => $filePath,
        'config_filename' => basename($filePath),
        'config_filesize' => filesize($filePath),
        'config_version' => $version,
        'latest_version' => $latest,
    ]);
}

test('a real change dispatches the notification job', function () {
    Bus::fake();

    notificationMakeConfig($this->command, notificationWriteFile($this->workDir, 'prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);
    $current = notificationMakeConfig($this->command, notificationWriteFile($this->workDir, 'curr.txt', "hostname r1\ninterface g0/0\n ip address 10.0.0.1 255.255.255.0\n"), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    Bus::assertDispatched(ConfigChangedNotificationJob::class);
});

test('no change does not dispatch the notification job', function () {
    Bus::fake();

    $content = "hostname r1\ninterface g0/0\n";
    notificationMakeConfig($this->command, notificationWriteFile($this->workDir, 'prev.txt', $content), 1, 0);
    $current = notificationMakeConfig($this->command, notificationWriteFile($this->workDir, 'curr.txt', $content), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    Bus::assertNotDispatched(ConfigChangedNotificationJob::class);
});

test('job emails users opted into config change alerts', function () {
    Notification::fake();

    $optedIn = User::factory()->create();
    $optedIn->setNotificationPreference(NotificationType::CONFIG_CHANGED, NotificationChannel::MAIL, true);

    $optedOut = User::factory()->create();
    $optedOut->setNotificationPreference(NotificationType::CONFIG_CHANGED, NotificationChannel::MAIL, false);

    $config = notificationMakeConfig($this->command, notificationWriteFile($this->workDir, 'curr.txt', "hostname r1\n"), 2, 1);

    (new ConfigChangedNotificationJob($config))->handle();

    Notification::assertSentTo($optedIn, MailConfigChangedNotification::class);
    Notification::assertNotSentTo($optedOut, MailConfigChangedNotification::class);
});
