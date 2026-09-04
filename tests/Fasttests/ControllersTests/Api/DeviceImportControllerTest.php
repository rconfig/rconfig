<?php

use App\Exports\DeviceImportTemplateExport;
use App\Http\Controllers\Api\DeviceImportController;
use App\Models\Category;
use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    foreach (glob(base_path('tests/storage/import_*.csv')) as $tmp) {
        @unlink($tmp);
    }

    $this->rollBackTransaction();
});

/**
 * Create the reference data (category, vendor, template, tags) that a valid
 * import row points at, using IDs that will not collide with seeded data.
 */
function createReferenceData(): void
{
    Category::factory()->create(['id' => 9001]);
    Vendor::factory()->create(['id' => 9001]);
    Template::factory()->create(['id' => 9001]);
    Tag::factory()->create(['id' => 9001]);
    Tag::factory()->create(['id' => 9002]);
}

/**
 * Build an uploadable CSV file from header + rows.
 *
 * @param  array<int, string>  $header
 * @param  array<int, array<int, string>>  $rows
 */
function makeCsvUpload(array $header, array $rows): UploadedFile
{
    $handle = fopen('php://temp', 'r+');
    fputcsv($handle, $header);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    rewind($handle);
    $contents = stream_get_contents($handle);
    fclose($handle);

    $path = base_path('tests/storage/import_' . uniqid() . '.csv');
    file_put_contents($path, $contents);

    return new UploadedFile($path, 'devices_import.csv', 'text/csv', null, true);
}

/**
 * @return array<int, string>
 */
function deviceImportCsvHeader(): array
{
    return [
        'device_name', 'device_ip', 'device_username', 'device_password', 'device_enable_password',
        'device_main_prompt', 'device_enable_prompt', 'device_category_id', 'device_template',
        'device_model', 'device_vendor', 'device_tag', 'device_default_creds_on', 'device_cred_id',
    ];
}

test('device import template is generated locally for download', function () {
    Excel::fake();

    $response = $this->get('/download-import-template');
    $response->assertStatus(200);

    Excel::assertDownloaded('device_import_template.xlsx', function (DeviceImportTemplateExport $export) {
        return in_array('device_name', $export->headings())
            && in_array('device_tag', $export->headings());
    });
});

test('device import template downloads a real xlsx file', function () {
    // No Excel::fake() here on purpose: this exercises the real laravel-excel
    // temp-file -> download pipeline and guards against the temp-path
    // permission error that used to break the download.
    $response = $this->get('/download-import-template');

    $response->assertStatus(200);
    $response->assertDownload('device_import_template.xlsx');
    expect($response->streamedContent())->not->toBeEmpty();
});

test('a device import requires a file', function () {
    $response = $this->json('post', '/api/settings/import/devices', ['file' => null]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('file');
});

test('filter for valid columns only removes unknown and blank columns', function () {
    $test_array = [
        'device_name' => 'sss',
        'device_ip' => '1.1.1.1',
        'device_username' => 'asasf',
        'device_password' => 'asfa',
        'device_enable_password' => 'asf',
        'device_main_prompt' => 'asf',
        'device_enable_prompt' => 'asf',
        'device_category_id' => 1,
        'device_template' => 1,
        'device_model' => 1111,
        'device_vendor' => 1,
        'device_tag' => '1,2',
        '' => ' ',
        'not_a_real_column' => 'should be removed',
    ];

    $result = (new DeviceImportController(new Device, 'device'))->filterForValidColumnsOnly($test_array);

    $this->assertArrayNotHasKey('', $result);
    $this->assertArrayNotHasKey('not_a_real_column', $result);
    expect($result)->toHaveKey('device_name');
});

test('upload import file imports good rows and reports deliberate errors', function () {
    createReferenceData();

    $file = makeCsvUpload(deviceImportCsvHeader(), [
        ['device1', '1.1.1.1', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['device2', '2.2.2.2', 'u', 'p', 'e', 'router2#', 'router2>', '9001', '9001', 'modelX', '9001', '9001,9002', '0', '0'],
        ['device3', '', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['device4', '4.4.4.4', 'u', 'p', 'e', 'router1$', 'router1#', '999999', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['device5', '5.5.5.5', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '999999', 'modelX', '9001', '9001', '0', '0'],
        ['device6', '6.6.6.6', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '999999', '9001', '0', '0'],
        ['device7', '7.7.7.7', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '999999', '0', '0'],
        ['device 8', '8.8.8.8', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
    ]);

    $response = $this->json('POST', '/api/settings/import/devices', ['file' => $file]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => '3 Devices successfully imported']);
    $response->assertJsonFragment(['Import Error device3: Blank Fields']);
    $response->assertJsonFragment(['Import Error device4: Invalid Category ID']);
    $response->assertJsonFragment(['Import Error device5: Invalid Template ID']);
    $response->assertJsonFragment(['Import Error device6: Invalid Vendor ID']);
    $response->assertJsonFragment(['Import Error device7: Invalid Tag ID']);

    $this->assertDatabaseHas('devices', ['device_name' => 'device1', 'device_ip' => '1.1.1.1', 'status' => 2]);
    $this->assertDatabaseHas('devices', ['device_name' => 'device2', 'device_ip' => '2.2.2.2']);

    // spaces stripped from "device 8"
    $this->assertDatabaseHas('devices', ['device_name' => 'device8', 'device_ip' => '8.8.8.8']);

    // bad rows must not be persisted
    $this->assertDatabaseMissing('devices', ['device_name' => 'device3']);
    $this->assertDatabaseMissing('devices', ['device_name' => 'device4']);

    // tag pivots
    $device1 = DB::table('devices')->where('device_name', 'device1')->first();
    $this->assertDatabaseHas('device_tag', ['device_id' => $device1->id, 'tag_id' => 9001]);

    $device2 = DB::table('devices')->where('device_name', 'device2')->first();
    $this->assertDatabaseHas('device_tag', ['device_id' => $device2->id, 'tag_id' => 9001]);
    $this->assertDatabaseHas('device_tag', ['device_id' => $device2->id, 'tag_id' => 9002]);

    $this->assertDatabaseHas('activity_log', ['description' => 'Imported device1 successfully']);
    $this->assertDatabaseHas('activity_log', ['description' => 'Import Error device3: Blank Fields']);
});

test('upload import uses default device credentials when enabled', function () {
    createReferenceData();
    $creds = DeviceCredentials::factory()->create(['id' => 5001]);

    $file = makeCsvUpload(deviceImportCsvHeader(), [
        ['device10', '1.1.1.1', 'ignored', 'ignored', 'ignored', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '1', '5001'],
    ]);

    $response = $this->json('POST', '/api/settings/import/devices', ['file' => $file]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => '1 Devices successfully imported']);

    $this->assertDatabaseHas('devices', [
        'device_name' => 'device10',
        'device_default_creds_on' => 1,
        'device_cred_id' => 5001,
        'device_username' => $creds->cred_username,
        'status' => 2,
    ]);
});

test('import hostnames keep dots dashes underscores and strip invalid characters', function () {
    createReferenceData();

    $file = makeCsvUpload(deviceImportCsvHeader(), [
        ['device10.rconfig.com', '1.1.1.1', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['device-1.rconfig.com', '2.2.2.2', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['device1-1-2_rconfig_com', '3.3.3.3', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
        ['dev&30', '4.4.4.4', 'u', 'p', 'e', 'router1$', 'router1#', '9001', '9001', 'modelX', '9001', '9001', '0', '0'],
    ]);

    $response = $this->json('POST', '/api/settings/import/devices', ['file' => $file]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => '4 Devices successfully imported']);

    $this->assertDatabaseHas('devices', ['device_name' => 'device10.rconfig.com']);
    $this->assertDatabaseHas('devices', ['device_name' => 'device-1.rconfig.com']);
    $this->assertDatabaseHas('devices', ['device_name' => 'device1-1-2_rconfig_com']);

    // invalid characters stripped
    $this->assertDatabaseHas('devices', ['device_name' => 'dev30']);
});
