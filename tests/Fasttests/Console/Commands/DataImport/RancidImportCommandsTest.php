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

    $this->mappingsFile = storage_path('app/rconfig/rancid_mappings.json');
    $this->tempDir = storage_path('app/rconfig/tempdir');

    $dir = storage_path('app/rconfig');
    if (! File::exists($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir, 0755, true);
    }

    $this->template = Template::factory()->create(['templateName' => 'rancid_test_template']);
    $this->vendor = Vendor::factory()->create(['vendorName' => 'rancid_test_vendor']);
    $this->category = Category::factory()->create(['categoryName' => 'rancid_test_category']);
    $this->credential = DeviceCredentials::factory()->create([
        'cred_name' => 'rancid_test_cred',
        'cred_description' => 'RANCID Test Credentials',
    ]);
    $this->tag = Tag::factory()->create(['tagname' => 'rancid_test_tag']);
});

test('rancid device mappings command exists', function () {
    $exitCode = $this->artisan('rconfig:rancid-device-mappings --info')->run();
    expect($exitCode)->toEqual(0);
});

test('rancid device mappings creates empty file', function () {
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:rancid-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();

    $content = json_decode(File::get($this->mappingsFile), true);
    expect($content)->toBeArray();
    expect($content)->toBeEmpty();
});

test('rancid device mappings can list existing', function () {
    $mappings = [
        'cisco' => [
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

    $exitCode = $this->artisan('rconfig:rancid-device-mappings --list')->run();
    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    expect($loaded)->toHaveKey('cisco');
});

test('rancid import devices fails with missing file', function () {
    $nonExistentFile = $this->tempDir . '/does_not_exist.json';

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $nonExistentFile])->run();
    expect($exitCode)->toEqual(1);
});

test('rancid import devices fails with invalid json', function () {
    $invalidJsonFile = $this->tempDir . '/invalid.json';
    File::put($invalidJsonFile, 'this is not valid json');

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $invalidJsonFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($invalidJsonFile);
});

test('rancid import devices fails with empty array', function () {
    $emptyFile = $this->tempDir . '/empty.json';
    File::put($emptyFile, json_encode([]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $emptyFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($emptyFile);
});

test('rancid import devices dry run mode', function () {
    $validDevice = [
        [
            'device_name' => 'test-router-01',
            'device_ip' => '10.1.1.1',
            'device_model' => 'Cisco 7606',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'test-router-01>',
                'device_main_prompt' => 'test-router-01#',
            ],
            'tags' => [$this->tag->id],
            'rancid_group' => 'networking',
            'connection_type' => 'ssh',
            'port' => 22,
        ],
    ];

    $validFile = $this->tempDir . '/valid_device.json';
    File::put($validFile, json_encode($validDevice));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', [
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
function rancidValidImportDevice(Template $template, Vendor $vendor, Category $category, DeviceCredentials $credential, Tag $tag, array $overrides = []): array
{
    return array_merge([
        'device_name' => 'rancid-real-01',
        'device_ip' => '10.20.30.40',
        'device_model' => 'Cisco 7606',
        'template_id' => $template->id,
        'vendor_id' => $vendor->id,
        'device_category_id' => $category->id,
        'device_cred_id' => $credential->id,
        'prompts' => [
            'device_enable_prompt' => 'rancid-real-01>',
            'device_main_prompt' => 'rancid-real-01#',
        ],
        'tags' => [$tag->id],
        'rancid_group' => 'networking',
    ], $overrides);
}

test('rancid import creates device with pivots', function () {
    $file = $this->tempDir . '/real_device.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Return to main menu?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseHas('devices', [
        'device_name' => 'rancid-real-01',
        'device_ip' => '10.20.30.40',
        'device_model' => 'Cisco 7606',
        'device_template' => $this->template->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'device_main_prompt' => 'rancid-real-01#',
        'device_enable_prompt' => 'rancid-real-01>',
        'status' => 1,
    ]);

    $created = Device::where('device_name', 'rancid-real-01')->firstOrFail();
    expect($created->Template()->where('templates.id', $this->template->id)->exists())->toBeTrue();
    expect($created->Vendor()->where('vendors.id', $this->vendor->id)->exists())->toBeTrue();
    expect($created->Category()->where('categories.id', $this->category->id)->exists())->toBeTrue();
    expect($created->Tag()->where('tags.id', $this->tag->id)->exists())->toBeTrue();

    File::delete($file);
});

test('rancid import skips duplicate device', function () {
    Device::factory()->create([
        'device_name' => 'rancid-real-01',
        'device_ip' => '10.20.30.40',
    ]);

    $file = $this->tempDir . '/dup_device.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    // Existing device fails validation (duplicate), so no valid devices remain.
    expect($exitCode)->toEqual(1);
    expect(Device::where('device_name', 'rancid-real-01')->count())->toEqual(1);

    File::delete($file);
});

test('rancid import rejects invalid ip', function () {
    $file = $this->tempDir . '/bad_ip.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_ip' => 'not-an-ip'])]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'rancid-real-01']);

    File::delete($file);
});

test('rancid import rejects nonexistent template', function () {
    $file = $this->tempDir . '/bad_template.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['template_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'rancid-real-01']);

    File::delete($file);
});

test('rancid import rejects nonexistent vendor', function () {
    $file = $this->tempDir . '/bad_vendor.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['vendor_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'rancid-real-01']);

    File::delete($file);
});

test('rancid import rejects nonexistent category', function () {
    $file = $this->tempDir . '/bad_category.json';
    File::put($file, json_encode([rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_category_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'rancid-real-01']);

    File::delete($file);
});

test('rancid import rejects missing prompts', function () {
    $device = rancidValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag);
    unset($device['prompts']);

    $file = $this->tempDir . '/no_prompts.json';
    File::put($file, json_encode([$device]));

    $exitCode = $this->artisan('rconfig:rancid-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'rancid-real-01']);

    File::delete($file);
});

test('rancid load devices fails without mappings file', function () {
    // Point at an existing directory containing a router.db group so the
    // command gets past the base-path discovery, then fails on missing mappings.
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $base = $this->tempDir . '/rancid_base';
    File::makeDirectory($base . '/networking', 0755, true);
    File::put($base . '/networking/router.db', "10.0.0.1:cisco:up\n");

    $exitCode = $this->artisan('rconfig:rancid-load-devices', [
        '--rancid-base' => $base,
        '--group' => 'networking',
    ])
        ->expectsConfirmation('Would you like to create device mappings now?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);

    File::deleteDirectory($base);
});

test('rancid load devices parses router db with ip hosts', function () {
    // Using IP "hostnames" avoids DNS resolution (resolveHostname returns the IP directly).
    $base = $this->tempDir . '/rancid_base';
    File::makeDirectory($base . '/networking/configs', 0755, true);
    File::put($base . '/networking/router.db', "10.0.0.1:cisco:up\n10.0.0.2:cisco:down\n# comment\n");
    File::put($base . '/networking/configs/10.0.0.1', "hostname core1\n");

    File::put($this->mappingsFile, json_encode([
        'cisco' => [
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
    ], JSON_PRETTY_PRINT));

    // defaultCredentialId is resolved interactively (askForDefaultCredential),
    // then a view-summary confirm and an import-now confirm follow.
    $exitCode = $this->artisan('rconfig:rancid-load-devices', [
        '--rancid-base' => $base,
        '--group' => 'networking',
    ])
        ->expectsQuestion('Which credential would you like to use as default?', $this->credential->id)
        ->expectsConfirmation('Would you like to view a summary of the imported devices?', 'no')
        ->expectsConfirmation('Would you like to import these devices into rConfig now?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);

    $files = glob($this->tempDir . '/rconfig_import_*.json');
    expect($files)->not->toBeEmpty();

    $loaded = json_decode(File::get($files[0]), true);
    expect($loaded)->toHaveCount(1);
    // only the "up" device with a config file
    expect($loaded[0]['device_name'])->toEqual('10.0.0.1');
    expect($loaded[0]['device_ip'])->toEqual('10.0.0.1');
    expect($loaded[0]['rancid_group'])->toEqual('networking');
    expect($loaded[0]['template_id'])->toEqual($this->template->id);
    expect($loaded[0]['tags'])->toEqual([$this->tag->id]);

    File::deleteDirectory($base);
});

test('rancid mappings delete removes entry', function () {
    File::put($this->mappingsFile, json_encode([
        'cisco' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
        'juniper' => ['device_type' => 'junos', 'template_id' => $this->template->id],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:rancid-device-mappings', ['--delete' => 'cisco'])
        ->expectsConfirmation("Are you sure you want to delete the mapping for 'cisco'?", 'yes')
        ->run();

    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    $this->assertArrayNotHasKey('cisco', $loaded);
    expect($loaded)->toHaveKey('juniper');
});

test('rancid mappings delete unknown entry leaves file intact', function () {
    File::put($this->mappingsFile, json_encode([
        'cisco' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:rancid-device-mappings', ['--delete' => 'nope'])->run();
    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    expect($loaded)->toHaveKey('cisco');
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
            } elseif (is_dir($file)) {
                File::deleteDirectory($file);
            }
        }
    }

    $this->rollBackTransaction();

});
