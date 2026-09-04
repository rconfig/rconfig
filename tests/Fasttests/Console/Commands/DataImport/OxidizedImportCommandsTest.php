<?php

use App\Models\Category;
use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->mappingsFile = storage_path('app/rconfig/oxidized_mappings.json');
    $this->tempDir = storage_path('app/rconfig/tempdir');

    $dir = storage_path('app/rconfig');
    if (! File::exists($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir, 0755, true);
    }

    $this->template = Template::factory()->create(['templateName' => 'oxidized_test_template']);
    $this->vendor = Vendor::factory()->create(['vendorName' => 'oxidized_test_vendor']);
    $this->category = Category::factory()->create(['categoryName' => 'oxidized_test_category']);
    $this->credential = DeviceCredentials::factory()->create([
        'cred_name' => 'oxidized_test_cred',
        'cred_description' => 'Oxidized Test Credentials',
    ]);
    $this->tag = Tag::factory()->create(['tagname' => 'oxidized_test_tag']);
});

test('oxidized device mappings command exists', function () {
    $exitCode = $this->artisan('rconfig:oxidized-device-mappings --info')->run();
    expect($exitCode)->toEqual(0);
});

test('oxidized device mappings creates empty file', function () {
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:oxidized-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();

    $content = json_decode(File::get($this->mappingsFile), true);
    expect($content)->toBeArray();
    expect($content)->toBeEmpty();
});

test('oxidized device mappings can list existing', function () {
    $mappings = [
        'ios' => [
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'prompts' => [
                'device_enable_prompt' => '{device_name}>',
                'device_main_prompt' => '{device_name}#',
            ],
            'tags' => [$this->tag->id],
            'device_type' => 'cisco_ios',
        ],
    ];

    File::put($this->mappingsFile, json_encode($mappings, JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:oxidized-device-mappings --list')->run();
    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    expect($loaded)->toHaveKey('ios');
});

test('oxidized import devices fails with missing file', function () {
    $nonExistentFile = $this->tempDir . '/does_not_exist.json';

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $nonExistentFile])->run();
    expect($exitCode)->toEqual(1);
});

test('oxidized import devices fails with invalid json', function () {
    $invalidJsonFile = $this->tempDir . '/invalid.json';
    File::put($invalidJsonFile, 'this is not valid json');

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $invalidJsonFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($invalidJsonFile);
});

test('oxidized import devices fails with empty array', function () {
    $emptyFile = $this->tempDir . '/empty.json';
    File::put($emptyFile, json_encode([]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $emptyFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($emptyFile);
});

test('oxidized import devices dry run mode', function () {
    $validDevice = [
        [
            'device_name' => 'test-router-01',
            'device_ip' => '10.1.1.1',
            'device_model' => 'cisco_ios',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'test-router-01>',
                'device_main_prompt' => 'test-router-01#',
            ],
            'tags' => [$this->tag->id],
            'connection_type' => 'ssh',
            'port' => 22,
        ],
    ];

    $validFile = $this->tempDir . '/valid_device.json';
    File::put($validFile, json_encode($validDevice));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', [
        'file' => $validFile,
        '--dry-run' => true,
    ])->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseMissing('devices', [
        'device_name' => 'test-router-01',
    ]);

    File::delete($validFile);
});

/**
 * Build a fully valid device payload for the import command.
 *
 * @return array<string, mixed>
 */
function oxidizedValidImportDevice(Template $template, Vendor $vendor, Category $category, DeviceCredentials $credential, Tag $tag, array $overrides = []): array
{
    return array_merge([
        'device_name' => 'oxidized-real-01',
        'device_ip' => '10.40.50.60',
        'device_model' => 'cisco_ios',
        'template_id' => $template->id,
        'vendor_id' => $vendor->id,
        'device_category_id' => $category->id,
        'device_cred_id' => $credential->id,
        'prompts' => [
            'device_enable_prompt' => 'oxidized-real-01>',
            'device_main_prompt' => 'oxidized-real-01#',
        ],
        'tags' => [$tag->id],
    ], $overrides);
}

test('oxidized import creates device with pivots', function () {
    $file = $this->tempDir . '/real_device.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Return to main menu?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseHas('devices', [
        'device_name' => 'oxidized-real-01',
        'device_ip' => '10.40.50.60',
        'device_template' => $this->template->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'status' => 1,
    ]);

    $created = Device::where('device_name', 'oxidized-real-01')->firstOrFail();
    expect($created->Template()->where('templates.id', $this->template->id)->exists())->toBeTrue();
    expect($created->Vendor()->where('vendors.id', $this->vendor->id)->exists())->toBeTrue();
    expect($created->Category()->where('categories.id', $this->category->id)->exists())->toBeTrue();
    expect($created->Tag()->where('tags.id', $this->tag->id)->exists())->toBeTrue();

    File::delete($file);
});

test('oxidized import skips duplicate device', function () {
    Device::factory()->create([
        'device_name' => 'oxidized-real-01',
        'device_ip' => '10.40.50.60',
    ]);

    $file = $this->tempDir . '/dup_device.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    expect(Device::where('device_name', 'oxidized-real-01')->count())->toEqual(1);

    File::delete($file);
});

test('oxidized import rejects invalid ip', function () {
    $file = $this->tempDir . '/bad_ip.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_ip' => 'not-an-ip'])]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

    File::delete($file);
});

test('oxidized import rejects nonexistent template', function () {
    $file = $this->tempDir . '/bad_template.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['template_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

    File::delete($file);
});

test('oxidized import rejects nonexistent vendor', function () {
    $file = $this->tempDir . '/bad_vendor.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['vendor_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

    File::delete($file);
});

test('oxidized import rejects nonexistent category', function () {
    $file = $this->tempDir . '/bad_category.json';
    File::put($file, json_encode([oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_category_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

    File::delete($file);
});

test('oxidized import rejects missing prompts', function () {
    $device = oxidizedValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag);
    unset($device['prompts']);

    $file = $this->tempDir . '/no_prompts.json';
    File::put($file, json_encode([$device]));

    $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

    File::delete($file);
});

test('oxidized load devices fails with missing input file', function () {
    $exitCode = $this->artisan('rconfig:oxidized-load-devices', [
        'file' => $this->tempDir . '/nope_hosts',
    ])->run();

    expect($exitCode)->toEqual(1);
});

test('oxidized load devices fails without mappings file', function () {
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $hostsFile = $this->tempDir . '/oxidized_hosts';
    File::put($hostsFile, "router1:ios\n");

    $exitCode = $this->artisan('rconfig:oxidized-load-devices', ['file' => $hostsFile])
        ->expectsConfirmation('Would you like to create device mappings now?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);

    File::delete($hostsFile);
});

test('oxidized mappings delete removes entry', function () {
    File::put($this->mappingsFile, json_encode([
        'ios' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
        'junos' => ['device_type' => 'junos', 'template_id' => $this->template->id],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:oxidized-device-mappings', ['--delete' => 'ios'])
        ->expectsConfirmation("Are you sure you want to delete the mapping for 'ios'?", 'yes')
        ->run();

    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    $this->assertArrayNotHasKey('ios', $loaded);
    expect($loaded)->toHaveKey('junos');
});

afterEach(function () {
    $filesToDelete = [$this->mappingsFile];

    foreach ($filesToDelete as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }

    if (File::exists($this->tempDir)) {
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    $this->rollBackTransaction();

});
