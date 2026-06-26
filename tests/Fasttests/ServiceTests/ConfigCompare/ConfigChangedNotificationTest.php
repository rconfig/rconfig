<?php

namespace Tests\Fasttests\ServiceTests\ConfigCompare;

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
use Tests\TestCase;

class ConfigChangedNotificationTest extends TestCase
{
    private string $command = 'show run notif test';
    private string $workDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        (new CompareExclusionTemplateService)->installDefaultTemplate();
        Command::firstOrCreate(['command' => $this->command]);

        $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('notif_', true) . '/');
        File::makeDirectory($this->workDir, 0777, true, true);
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();
        File::deleteDirectory($this->workDir);
        File::delete(File::glob(tmp_dir() . '/*.txt'));
        parent::tearDown();
    }

    private function writeFile(string $name, string $content): string
    {
        $path = $this->workDir . $name;
        File::put($path, $content);

        return $path;
    }

    private function makeConfig(string $filePath, ?int $version, int $latest): Config
    {
        return Config::create([
            'device_id' => 778899,
            'device_name' => 'notif-router',
            'device_category' => 'Routers',
            'command' => $this->command,
            'type' => 'device_download',
            'download_status' => 1,
            'config_location' => $filePath,
            'config_filename' => basename($filePath),
            'config_filesize' => filesize($filePath),
            'config_version' => $version,
            'latest_version' => $latest,
        ]);
    }

    public function test_a_real_change_dispatches_the_notification_job(): void
    {
        Bus::fake();

        $this->makeConfig($this->writeFile('prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);
        $current = $this->makeConfig($this->writeFile('curr.txt', "hostname r1\ninterface g0/0\n ip address 10.0.0.1 255.255.255.0\n"), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        Bus::assertDispatched(ConfigChangedNotificationJob::class);
    }

    public function test_no_change_does_not_dispatch_the_notification_job(): void
    {
        Bus::fake();

        $content = "hostname r1\ninterface g0/0\n";
        $this->makeConfig($this->writeFile('prev.txt', $content), 1, 0);
        $current = $this->makeConfig($this->writeFile('curr.txt', $content), null, 1);

        (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

        Bus::assertNotDispatched(ConfigChangedNotificationJob::class);
    }

    public function test_job_emails_users_opted_into_config_change_alerts(): void
    {
        Notification::fake();

        $optedIn = User::factory()->create();
        $optedIn->setNotificationPreference(NotificationType::CONFIG_CHANGED, NotificationChannel::MAIL, true);

        $optedOut = User::factory()->create();
        $optedOut->setNotificationPreference(NotificationType::CONFIG_CHANGED, NotificationChannel::MAIL, false);

        $config = $this->makeConfig($this->writeFile('curr.txt', "hostname r1\n"), 2, 1);

        (new ConfigChangedNotificationJob($config))->handle();

        Notification::assertSentTo($optedIn, MailConfigChangedNotification::class);
        Notification::assertNotSentTo($optedOut, MailConfigChangedNotification::class);
    }
}
