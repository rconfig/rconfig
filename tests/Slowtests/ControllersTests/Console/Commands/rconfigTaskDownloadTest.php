<?php

namespace Tests\Slowtests\ControllersTests\Console\Commands;

use App\CustomClasses\GetAndCheckCategoryIds;
use App\CustomClasses\GetAndCheckTagIds;
use App\CustomClasses\GetAndCheckTaskIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Models\Taskdownloadreport;
use App\Models\User;
use App\Services\Config\FileOperations;
use Artisan;
use File;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class rconfigTaskDownloadTest extends TestCase
{
    protected $user;
    protected $device;
    protected $tags;
    protected $device1_params_object;
    protected $transactionStarted = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->beginTransaction();
        $this->transactionStarted = true;

        $this->device1 = Device::where('id', 1001)->first();
        $this->device2 = Device::where('id', 1002)->first();
        $this->device5 = Device::where('id', 1005)->first();
        $this->device8 = Device::where('id', 1008)->first();

        // tags and relationships with devices are seeded in DeviceTableSeeder class

        $device1_params = new DeviceParams($this->device1->toArray());
        $this->device1_params_object = $device1_params->getAllDeviceParams();

        $this->user = User::factory()->create();
        /** @var mixed $this->user */
        $this->actingAs($this->user, 'api');
    }

    public function test_task_was_not_found()
    {
        Artisan::call('rconfig:download-task 2123123');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:2123123');
        $this->assertStringContainsString($arr[2], 'No task records returned. Download will now terminate!');
    }

    public function test_the_task_was_found_and_routers_task_has_devices()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories

        $this->assertCount(3, $taskrecords);
        $this->assertGreaterThan(0, $taskrecords[0]->device->count());
        $this->assertGreaterThan(0, $taskrecords[1]->tag->count());
        $this->assertGreaterThan(0, $taskrecords[2]->category->count());
    }

    public function test_the_test_task_5555555_has_two_devices()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories

        $this->assertCount(3, $taskrecords);
        $this->assertCount(2, $taskrecords[0]->device);
    }

    public function test_the_test_task_5555555_has_two_devices_and_can_download_both_of_them()
    {
        Artisan::call('rconfig:download-task 555555 -d');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:555555');
        $this->assertStringContainsString($arr[2], 'Start device download for router1 ID:1001');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router1 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router1 with command: "show version" was successful');
        $this->assertStringContainsString($arr[5], 'Config downloaded for router1 with command: "show run" was successful');
        $this->assertStringContainsString($arr[6], 'Start device download for router2 ID:1002');
        $this->assertStringContainsString($arr[7], 'Config downloaded for router2 with command: "show clock" was successful');
        $this->assertDatabaseHas('devices', [
            'id' => 1001,
            'status' => 1,
        ]);
    }

    public function test_the_test_task_666666_has_one_tag()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
        $this->assertCount(3, $taskrecords);
        $this->assertCount(1, $taskrecords[1]->tag);

        $tagrecords = (new GetAndCheckTagIds([1001, 1002, 1003]))->GetTagRecords();
    }

    public function test_the_test_task_666666_has_one_tag_and_the_tag_has_one_device()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories

        $this->assertCount(3, $taskrecords);
        $this->assertCount(1, $taskrecords[1]->tag);

        $tagrecords = (new GetAndCheckTagIds([$taskrecords[1]->tag[0]->id]))->GetTagRecords();
        $this->assertCount(1, $tagrecords);
        $this->assertCount(1, $tagrecords[0]->device);
    }

    public function test_the_test_task_666666_has_one_devices_and_can_download_it()
    {
        Artisan::call('rconfig:download-task 666666 -d');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:666666');
        $this->assertStringContainsString($arr[2], 'Start device download for router2 ID:1002');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router2 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router2 with command: "show version" was successful');
        $this->assertStringContainsString($arr[5], 'Config downloaded for router2 with command: "show run" was successful');
        $this->assertStringContainsString($arr[6], 'End rconfig:download-task');
        $this->assertDatabaseHas('devices', [
            'id' => 1001,
            'status' => 1,
        ]);
    }

    public function test_the_test_task_666666_has_one_devices_and_can_download_it_and_a_report_is_created()
    {
        // $this->markTestSkipped('Can only run this test manually - must be revisited.');

        Artisan::call('rconfig:download-task 666666 -d');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }
        sleep(10); // just sleeping while job is run to generate the report and log the results in the DB
        $task_report_record = Taskdownloadreport::where('task_id', 666666)->first();
        // dd($task_report_record);

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:666666');
        $this->assertStringContainsString($arr[2], 'Start device download for router2 ID:1002');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router2 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router2 with command: "show version" was successful');
        $this->assertStringContainsString($arr[5], 'Config downloaded for router2 with command: "show run" was successful');
        $this->assertStringContainsString($arr[6], 'End rconfig:download-task');
        $this->assertDatabaseHas('devices', [
            'id' => 1002,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('configs', [
            'device_id' => 1002,
            'download_status' => 1,
            'report_id' => $task_report_record->report_id,
            'command' => 'show version',
        ]);

        $this->assertDatabaseHas('taskdownloadreports', [
            'task_id' => 666666,
            'report_id' => $task_report_record->report_id,
            'file_name' => $task_report_record->report_id . '.html',
        ]);
        $filepath = report_path() . $task_report_record->report_id . '.html';

        $this->assertTrue(File::exists($filepath));
        // $this->assertStringContainsString('<h4><strong>Task ID/ Name:</strong> 666666 / DevTask2 </h4>\n', file_get_contents($filepath));
        // $this->assertStringContainsString('<td><a href="/device/view/1002">router2</a></td>', file_get_contents($filepath));
        File::delete($filepath);
    }

    public function test_the_test_task_7777777_has_three_categories()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories

        $this->assertCount(3, $taskrecords);
        $this->assertCount(3, $taskrecords[2]->category);
    }

    public function test_the_test_task_7777777_has_three_categories_and_x_devices_per_category()
    {
        $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords(); // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories

        $this->assertCount(3, $taskrecords);
        $this->assertCount(3, $taskrecords[2]->category);

        $categoryrecords = (new GetAndCheckCategoryIds($taskrecords[2]->category->pluck('id')->toArray()))->GetCategoryRecords(); // 1 = routers & 2 = switches which are seeded categories

        $this->assertCount(3, $categoryrecords);
        // $this->assertCount(1, $tagrecords[0]->device);
        $this->assertGreaterThan(6, count($categoryrecords[0]->device));
        $this->assertCount(0, $categoryrecords[1]->device);
        $this->assertCount(0, $categoryrecords[2]->device);
    }

    public function test_the_test_task_777777_has_four_devices_and_can_download_them()
    {
        Artisan::call('rconfig:download-task 777777 -d');
        $result = Artisan::output();

        $output = $result;

        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        // Verify expected output messages
        $this->assertStringContainsString('This operation can take some time, depending on how many devices are configured for this task!!!', $output);
        $this->assertStringContainsString('Start rconfig:download-task IDs:777777', $output);
        
        // Successful downloads (router1-4)
        $this->assertStringContainsString('Start device download for router1 ID:1001', $output);
        $this->assertStringContainsString('Config downloaded for router1 with command: "show clock" was successful', $output);
        $this->assertStringContainsString('Config downloaded for router1 with command: "show version" was successful', $output);
        $this->assertStringContainsString('Config downloaded for router1 with command: "show run" was successful', $output);
        
        $this->assertStringContainsString('Start device download for router2 ID:1002', $output);
        $this->assertStringContainsString('Start device download for router3 ID:1003', $output);
        $this->assertStringContainsString('Start device download for router4 ID:1004', $output);
        
        // Failed downloads (router5 and router1v6 devices)
        $this->assertStringContainsString('Start device download for router5 ID:1005', $output);
        $this->assertStringContainsString('No config data returned for router5 - ID:1005', $output);
        
        $this->assertStringContainsString('Start device download for router1v6 ID:1009', $output);
        $this->assertStringContainsString('No config data returned for router1v6 - ID:1009', $output);
        
        $this->assertStringContainsString('Start device download for router1v6 ID:1010', $output);
        $this->assertStringContainsString('No config data returned for router1v6 - ID:1010', $output);
        
        // Verify successful devices have status 1
        $this->assertDatabaseHas('devices', [
            'id' => 1001,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('devices', [
            'id' => 1002,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('devices', [
            'id' => 1003,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('devices', [
            'id' => 1004,
            'status' => 1,
        ]);
        
        // Verify failed devices have status 0
        $this->assertDatabaseHas('devices', [
            'id' => 1005,
            'status' => 0,  // Unreachable IP
        ]);
        $this->assertDatabaseHas('devices', [
            'id' => 1009,
            'status' => 0,  // IPv6 unreachable
        ]);
        $this->assertDatabaseHas('devices', [
            'id' => 1010,
            'status' => 0,  // IPv6 unreachable
        ]);
    }

    public function test_the_test_task_888888_has_one_device_and_cannot_download_and_sends_task_complete_notification_as_configured()
    {
        // test Task completion notification for given task
        \Queue::fake();
        // configure failure report only
        \DB::table('tasks')->where('id', 888888)->update(['task_email_notify' => 1]);

        $this->assertDatabaseHas('tasks', [
            'id' => 888888,
            'task_email_notify' => 1,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
        ]);

        Artisan::call('rconfig:download-task 888888 -d');
        $result = Artisan::output();
        // dd($result);
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:888888');
        $this->assertStringContainsString($arr[2], 'Start device download for router8 ID:1008');
        $this->assertStringContainsString($arr[3], 'No config data returned for router8 - ID:1008. Check your logs for more information');
        $this->assertDatabaseHas('devices', [
            'id' => 1008,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => 888888,
            'task_email_notify' => 1,
            'download_report_notify' => 0,
            'verbose_download_report_notify' => 0,
        ]);

        \Queue::assertPushed(\App\Jobs\TaskCompleteNotificationJob::class, 1);

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
            'data' => '{"title":"Device Connection Error","description":"No config data returned for router8 - ID:1008. Check your logs for more information","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
            'data' => '{"title":"Device Connection Error","description":"Unable to connect to 10.0.0.111 - ID:1008","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
        ]);
    }

    public function test_the_test_task_888888_has_one_device_and_cannot_download_and_send_failure_report_notify_as_configured()
    {
        \Queue::fake();
        // configure failure report only
        \DB::table('tasks')->where('id', 888888)->update(['download_report_notify' => 1]);

        $this->assertDatabaseHas('tasks', [
            'id' => 888888,
            'task_email_notify' => 1,
            'download_report_notify' => 1,
            'verbose_download_report_notify' => 0,
        ]);

        Artisan::call('rconfig:download-task 888888 -d');
        $result = Artisan::output();
        // dd($result);
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
        $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:888888');
        $this->assertStringContainsString($arr[2], 'Start device download for router8 ID:1008');
        $this->assertStringContainsString($arr[3], 'No config data returned for router8 - ID:1008. Check your logs for more information');
        $this->assertDatabaseHas('devices', [
            'id' => 1008,
            'status' => 0,
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => 888888,
            'task_email_notify' => 1,
            'download_report_notify' => 1,
            'verbose_download_report_notify' => 0,
        ]);
        \Queue::assertPushed(\App\Jobs\SendTaskReportNotificationJob::class, 1);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => 1,
            'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
            'data' => '{"title":"Device Connection Error","description":"No config data returned for router8 - ID:1008. Check your logs for more information","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
        ]);
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
            'data' => '{"title":"Device Connection Error","description":"Unable to connect to 10.0.0.111 - ID:1008","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
        ]);
        DB::table('tasks')->where('id', 888888)->update(['download_report_notify' => 0]);
    }

    private function downloaded_file_exists_on_disk($device, $command)
    {
        $fileops = new FileOperations(
            $command,
            $device['category'][0]['categoryName'],
            $device['device_name'],
            $device['id'],
            config_data_path(),
            'device_download'
        );

        $fullpath = $fileops->createFile($command);

        return File::exists($fullpath);
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            $this->rollBackTransaction();
        }

        DB::table('notifications')->truncate();
        DB::table('taskdownloadreports')->truncate();
        DB::table('tasks')->where('id', 888888)->update(['download_report_notify' => 0]);

        parent::tearDown();
    }
}
