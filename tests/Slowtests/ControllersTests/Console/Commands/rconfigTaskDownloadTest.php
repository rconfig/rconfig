<?php

use App\CustomClasses\GetAndCheckCategoryIds;
use App\CustomClasses\GetAndCheckTagIds;
use App\CustomClasses\GetAndCheckTaskIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Jobs\SendTaskReportNotificationJob;
use App\Jobs\TaskCompleteNotificationJob;
use App\Models\Device;
use App\Models\Taskdownloadreport;
use App\Models\User;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->transactionStarted = false;

    // Skip if integration tests not enabled
    if (! env('RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Set RUN_INTEGRATION_TESTS=true to run live task download integration tests');
    }

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
    $this->actingAs($this->user);
});

test('task was not found', function () {
    Artisan::call('rconfig:download-task 2123123');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'This operation can take some time, depending on how many devices are configured for this task!!!');
    $this->assertStringContainsString($arr[1], 'Start rconfig:download-task IDs:2123123');
    $this->assertStringContainsString($arr[2], 'No task records returned. Download will now terminate!');
});

test('the task was found and routers task has devices', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();

    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[0]->device->count())->toBeGreaterThan(0);
    expect($taskrecords[1]->tag->count())->toBeGreaterThan(0);
    expect($taskrecords[2]->category->count())->toBeGreaterThan(0);
});

test('the test task 5555555 has two devices', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();

    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[0]->device)->toHaveCount(2);
});

test('the test task 5555555 has two devices and can download both of them', function () {
    Artisan::call('rconfig:download-task 555555 -d');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
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
});

test('the test task 666666 has one tag', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();
    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[1]->tag)->toHaveCount(1);

    $tagrecords = (new GetAndCheckTagIds([1001, 1002, 1003]))->GetTagRecords();
});

test('the test task 666666 has one tag and the tag has one device', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();

    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[1]->tag)->toHaveCount(1);

    $tagrecords = (new GetAndCheckTagIds([$taskrecords[1]->tag[0]->id]))->GetTagRecords();
    expect($tagrecords)->toHaveCount(1);
    expect($tagrecords[0]->device)->toHaveCount(1);
});

test('the test task 666666 has one devices and can download it', function () {
    Artisan::call('rconfig:download-task 666666 -d');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
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
});

test('the test task 666666 has one devices and can download it and a report is created', function () {
    // $this->markTestSkipped('Can only run this test manually - must be revisited.');
    Artisan::call('rconfig:download-task 666666 -d');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }
    sleep(10);
    // just sleeping while job is run to generate the report and log the results in the DB
    $task_report_record = Taskdownloadreport::where('task_id', 666666)->first();

    // dd($task_report_record);
    expect(count($arr))->toBeGreaterThan(0);
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

    expect(File::exists($filepath))->toBeTrue();

    // $this->assertStringContainsString('<h4><strong>Task ID/ Name:</strong> 666666 / DevTask2 </h4>\n', file_get_contents($filepath));
    // $this->assertStringContainsString('<td><a href="/device/view/1002">router2</a></td>', file_get_contents($filepath));
    File::delete($filepath);
});

test('the test task 7777777 has three categories', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();

    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[2]->category)->toHaveCount(3);
});

test('the test task 7777777 has three categories and x devices per category', function () {
    $taskrecords = (new GetAndCheckTaskIds([555555, 666666, 777777]))->GetTaskRecords();

    // 555555 = DevTask1  & 666666 = DevTask2  which are seeded categories
    expect($taskrecords)->toHaveCount(3);
    expect($taskrecords[2]->category)->toHaveCount(3);

    $categoryrecords = (new GetAndCheckCategoryIds($taskrecords[2]->category->pluck('id')->toArray()))->GetCategoryRecords();

    // 1 = routers & 2 = switches which are seeded categories
    expect($categoryrecords)->toHaveCount(3);

    // $this->assertCount(1, $tagrecords[0]->device);
    expect(count($categoryrecords[0]->device))->toBeGreaterThan(6);
    expect($categoryrecords[1]->device)->toHaveCount(0);
    expect($categoryrecords[2]->device)->toHaveCount(0);
});

test('the test task 777777 has four devices and can download them', function () {
    Artisan::call('rconfig:download-task 777777 -d');
    $result = Artisan::output();

    $output = $result;

    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
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

    // Failed downloads (router5)
    $this->assertStringContainsString('Start device download for router5 ID:1005', $output);
    $this->assertStringContainsString('No config data returned for router5 - ID:1005', $output);

    // router1v6 devices reach the same Cisco device over IPv6 and download successfully (category 1 commands)
    $this->assertStringContainsString('Start device download for router1v6 ID:1009', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show run" was successful', $output);

    $this->assertStringContainsString('Start device download for router1v6 ID:1010', $output);

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
    $this->assertDatabaseHas('devices', [
        'id' => 1009,
        'status' => 1,  // IPv6 device reachable
    ]);
    $this->assertDatabaseHas('devices', [
        'id' => 1010,
        'status' => 1,  // IPv6 device reachable
    ]);

    // Verify failed devices have status 0
    $this->assertDatabaseHas('devices', [
        'id' => 1005,
        'status' => 0,  // Unreachable IP
    ]);
});

test('the test task 888888 has one device and cannot download and sends task complete notification as configured', function () {
    // test Task completion notification for given task
    Queue::fake();

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
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
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

    Queue::assertPushed(TaskCompleteNotificationJob::class, 1);

    $this->assertDatabaseHas('notifications', [
        'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
        'data' => '{"title":"Device Connection Error","description":"No config data returned for router8 - ID:1008. Check your logs for more information","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
    ]);
    $this->assertDatabaseHas('notifications', [
        'type' => 'App\Notifications\DBDeviceConnectionFailureNotification',
        'data' => '{"title":"Device Connection Error","description":"Unable to connect to 10.0.0.111 - ID:1008","category":"downloader","error":"error","icon":"pficon-error-circle-o"}',
    ]);
});

test('the test task 888888 has one device and cannot download and send failure report notify as configured', function () {
    Queue::fake();

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
        if (! empty($match)) {
            expect(taskDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
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
    Queue::assertPushed(SendTaskReportNotificationJob::class, 1);

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
});

function taskDownloadFileExistsOnDisk($device, $command)
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

afterEach(function () {
    if ($this->transactionStarted) {
        $this->rollBackTransaction();
    }

    DB::table('notifications')->truncate();
    DB::table('taskdownloadreports')->truncate();
    DB::table('tasks')->where('id', 888888)->update(['download_report_notify' => 0]);

});
