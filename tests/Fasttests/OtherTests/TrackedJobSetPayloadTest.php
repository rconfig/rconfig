<?php

use App\Jobs\DeviceDownloadJob;
use App\Jobs\DownloadConfigNowJob;
use App\Models\TrackedJob;
use Illuminate\Support\Fluent;

/**
 * setPayload() reads the device id off the queued job, including the protected
 * DeviceDownloadJob::$devicerecord, through reflection.
 */
test('setPayload records the device id of a queued download-now job', function () {
    $trackedJob = TrackedJob::factory()->create(['device_id' => null]);
    $command = serialize(new DownloadConfigNowJob(4321));

    $trackedJob->setPayload(['displayName' => DownloadConfigNowJob::class, 'data' => ['command' => $command]]);

    expect($trackedJob->fresh()->device_id)->toBe(4321)
        ->and($trackedJob->fresh()->command)->toBe($command);
});

test('setPayload reads the device id from the protected device record of a device download job', function () {
    $trackedJob = TrackedJob::factory()->create(['device_id' => null]);
    $job = new DeviceDownloadJob(new Fluent(['id' => 8765, 'device_name' => 'TestDevice']), 'device', false);

    $trackedJob->setPayload(['displayName' => DeviceDownloadJob::class, 'data' => ['command' => serialize($job)]]);

    expect($trackedJob->fresh()->device_id)->toBe(8765);
});

test('setPayload leaves the device id empty for other jobs', function () {
    $trackedJob = TrackedJob::factory()->create(['device_id' => null]);

    $trackedJob->setPayload(['displayName' => 'App\Jobs\SomeOtherJob', 'data' => ['command' => 'x']]);

    expect($trackedJob->fresh()->device_id)->toBeNull();
});
