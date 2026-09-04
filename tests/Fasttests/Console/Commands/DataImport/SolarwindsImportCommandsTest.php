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

    $this->connectionFile = storage_path('app/rconfig/solarwinds_connection.json');
    $this->mappingsFile = storage_path('app/rconfig/solarwinds_mappings.json');
    $this->tempDir = storage_path('app/rconfig/tempdir');

    $dir = storage_path('app/rconfig');
    if (! File::exists($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir, 0755, true);
    }

    $this->template = Template::factory()->create(['templateName' => 'solarwinds_test_template']);
    $this->vendor = Vendor::factory()->create(['vendorName' => 'solarwinds_test_vendor']);
    $this->category = Category::factory()->create(['categoryName' => 'solarwinds_test_category']);
    $this->credential = DeviceCredentials::factory()->create([
        'cred_name' => 'solarwinds_test_cred',
        'cred_description' => 'SolarWinds Test Credentials',
    ]);
    $this->tag = Tag::factory()->create(['tagname' => 'solarwinds_test_tag']);
});

test('solarwinds connection command exists', function () {
    $exitCode = $this->artisan('rconfig:solarwinds-connection --info')->run();
    expect($exitCode)->toEqual(0);
});

test('solarwinds connection creates stub file', function () {
    $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');

    $this->artisan('rconfig:solarwinds-connection --info')->run();

    expect($stubFile)->toBeFile();

    $stub = json_decode(File::get($stubFile), true);
    expect($stub)->toHaveKey('swis_url');
    expect($stub)->toHaveKey('username');
    expect($stub)->toHaveKey('filters');
});

test('solarwinds device mappings command exists', function () {
    $exitCode = $this->artisan('rconfig:solarwinds-device-mappings --info')->run();
    expect($exitCode)->toEqual(0);
});

test('solarwinds device mappings creates empty file', function () {
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:solarwinds-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();

    $content = json_decode(File::get($this->mappingsFile), true);
    expect($content)->toBeArray();
    expect($content)->toBeEmpty();
});

test('solarwinds device mappings can list existing', function () {
    $mappings = [
        'Cisco IOS' => [
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'credential_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => '{device_name}>',
                'device_main_prompt' => '{device_name}#',
            ],
            'tags' => [$this->tag->id],
            'device_type' => 'cisco_ios',
            'custom_property_tag_mapping' => [],
            'node_group_mapping' => [],
        ],
    ];

    File::put($this->mappingsFile, json_encode($mappings, JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-device-mappings --list')->run();
    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    expect($loaded)->toHaveKey('Cisco IOS');
});

test('solarwinds load devices command exists', function () {
    $exitCode = $this->artisan('rconfig:solarwinds-load-devices --info')->run();
    expect($exitCode)->toEqual(0);
});

test('solarwinds import devices fails with missing file', function () {
    $nonExistentFile = $this->tempDir . '/does_not_exist.json';

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $nonExistentFile])->run();
    expect($exitCode)->toEqual(1);
});

test('solarwinds import devices fails with invalid json', function () {
    $invalidJsonFile = $this->tempDir . '/invalid.json';
    File::put($invalidJsonFile, 'this is not valid json');

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $invalidJsonFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($invalidJsonFile);
});

test('solarwinds import devices fails with empty array', function () {
    $emptyFile = $this->tempDir . '/empty.json';
    File::put($emptyFile, json_encode([]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $emptyFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($emptyFile);
});

test('solarwinds import devices dry run with valid device', function () {
    $validDevice = [
        [
            'device_name' => 'test-switch-01',
            'device_ip' => '10.1.1.1',
            'device_model' => 'Cisco IOS',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'test-switch-01>',
                'device_main_prompt' => 'test-switch-01#',
            ],
            'tags' => [$this->tag->id],
            'solarwinds_machine_type' => 'Cisco IOS',
            'solarwinds_node_groups' => ['Core Routers'],
            'solarwinds_custom_properties' => ['Location' => 'DC-East'],
            'connection_type' => 'ssh',
            'port' => 22,
        ],
    ];

    $validFile = $this->tempDir . '/valid_device.json';
    File::put($validFile, json_encode($validDevice));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', [
        'file' => $validFile,
        '--dry-run' => true,
    ])->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseMissing('devices', [
        'device_name' => 'test-switch-01',
    ]);

    File::delete($validFile);
});

test('solarwinds workflow file dependencies', function () {
    $this->artisan('rconfig:solarwinds-connection --info')->run();
    $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
    expect($stubFile)->toBeFile();

    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:solarwinds-device-mappings --list')->run();
    expect($this->mappingsFile)->toBeFile();

    $mappings = json_decode(File::get($this->mappingsFile), true);
    expect($mappings)->toBeArray();
});

test('solarwinds connection stub has proper structure', function () {
    $this->artisan('rconfig:solarwinds-connection --info')->run();

    $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
    $stub = json_decode(File::get($stubFile), true);

    expect($stub)->toHaveKey('swis_url');
    expect($stub)->toHaveKey('username');
    expect($stub)->toHaveKey('verify_ssl');
    expect($stub)->toHaveKey('filters');
    expect($stub)->toHaveKey('_comment');
    expect($stub)->toHaveKey('_instructions');
});

test('solarwinds mappings preserves structure', function () {
    $mapping = [
        'Cisco IOS' => [
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'credential_id' => $this->credential->id,
            'default_group_id' => 1,
            'prompts' => [
                'device_enable_prompt' => '{device_name}>',
                'device_main_prompt' => '{device_name}#',
            ],
            'tags' => [$this->tag->id],
            'device_type' => 'cisco_ios',
            'custom_property_tag_mapping' => [
                'Location' => 10,
                'Role' => 15,
            ],
            'node_group_mapping' => [
                'Core Routers' => 1,
                'Distribution' => 2,
            ],
        ],
    ];

    File::put($this->mappingsFile, json_encode($mapping, JSON_PRETTY_PRINT));

    $loaded = json_decode(File::get($this->mappingsFile), true);

    expect($loaded)->toHaveKey('Cisco IOS');
    expect($loaded['Cisco IOS']['template_id'])->toEqual($this->template->id);
    expect($loaded['Cisco IOS']['credential_id'])->toEqual($this->credential->id);
    expect($loaded['Cisco IOS'])->toHaveKey('custom_property_tag_mapping');
    expect($loaded['Cisco IOS']['custom_property_tag_mapping']['Location'])->toEqual(10);
    expect($loaded['Cisco IOS'])->toHaveKey('node_group_mapping');
    expect($loaded['Cisco IOS']['node_group_mapping']['Core Routers'])->toEqual(1);
});

test('solarwinds json structure is correct', function () {
    $device = [
        'device_name' => 'test-device',
        'device_ip' => '10.1.1.1',
        'device_model' => 'cisco_ios',
        'template_id' => $this->template->id,
        'vendor_id' => $this->vendor->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'prompts' => [
            'device_enable_prompt' => 'test>',
            'device_main_prompt' => 'test#',
        ],
        'tags' => [$this->tag->id],
        'solarwinds_machine_type' => 'Cisco IOS',
        'solarwinds_node_groups' => ['Core'],
        'solarwinds_custom_properties' => ['Location' => 'DC1'],
    ];

    $file = $this->tempDir . '/structure_test.json';
    File::put($file, json_encode([$device]));

    $loaded = json_decode(File::get($file), true);

    expect($loaded)->toBeArray();
    expect($loaded)->toHaveCount(1);
    expect($loaded[0]['device_name'])->toEqual('test-device');
    expect($loaded[0]['solarwinds_machine_type'])->toEqual('Cisco IOS');
    expect($loaded[0])->toHaveKey('solarwinds_node_groups');
    expect($loaded[0])->toHaveKey('solarwinds_custom_properties');

    File::delete($file);
});

test('solarwinds mappings file location is correct', function () {
    $this->artisan('rconfig:solarwinds-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();
    expect($this->mappingsFile)->toEqual(storage_path('app/rconfig/solarwinds_mappings.json'));
});

test('solarwinds connection filters structure', function () {
    $this->artisan('rconfig:solarwinds-connection --info')->run();

    $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
    $stub = json_decode(File::get($stubFile), true);

    expect($stub)->toHaveKey('filters');
    expect($stub['filters'])->toHaveKey('include_groups');
    expect($stub['filters'])->toHaveKey('exclude_groups');
    expect($stub['filters'])->toHaveKey('include_machine_types');
    expect($stub['filters'])->toHaveKey('exclude_machine_types');
    expect($stub['filters'])->toHaveKey('include_statuses');
    expect($stub['filters'])->toHaveKey('custom_property_filters');
});

test('solarwinds custom property mapping in output', function () {
    $device = [
        'device_name' => 'test-device',
        'device_ip' => '10.1.1.1',
        'device_model' => 'cisco_ios',
        'template_id' => $this->template->id,
        'vendor_id' => $this->vendor->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'prompts' => [
            'device_enable_prompt' => 'test>',
            'device_main_prompt' => 'test#',
        ],
        'tags' => [$this->tag->id, 10, 15], // Including custom property mapped tags
        'solarwinds_machine_type' => 'Cisco IOS',
        'solarwinds_custom_properties' => [
            'Location' => 'DC-East',
            'Role' => 'Core',
        ],
    ];

    $file = $this->tempDir . '/custom_prop_test.json';
    File::put($file, json_encode([$device]));

    $loaded = json_decode(File::get($file), true);

    expect($loaded[0])->toHaveKey('solarwinds_custom_properties');
    expect($loaded[0]['solarwinds_custom_properties']['Location'])->toEqual('DC-East');
    expect($loaded[0]['tags'])->toContain(10);
    expect($loaded[0]['tags'])->toContain(15);

    File::delete($file);
});

/**
 * Build a fully valid device payload for the import command.
 *
 * @return array<string, mixed>
 */
function solarwindsValidImportDevice(Template $template, Vendor $vendor, Category $category, DeviceCredentials $credential, Tag $tag, array $overrides = []): array
{
    return array_merge([
        'device_name' => 'sw-real-01',
        'device_ip' => '10.60.70.80',
        'device_model' => 'Cisco IOS',
        'template_id' => $template->id,
        'vendor_id' => $vendor->id,
        'device_category_id' => $category->id,
        'device_cred_id' => $credential->id,
        'prompts' => [
            'device_enable_prompt' => 'sw-real-01>',
            'device_main_prompt' => 'sw-real-01#',
        ],
        'tags' => [$tag->id],
        'solarwinds_machine_type' => 'Cisco IOS',
    ], $overrides);
}

test('solarwinds import creates device with pivots', function () {
    $file = $this->tempDir . '/real_device.json';
    File::put($file, json_encode([solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Return to main menu?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseHas('devices', [
        'device_name' => 'sw-real-01',
        'device_ip' => '10.60.70.80',
        'device_template' => $this->template->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'status' => 1,
    ]);

    $created = Device::where('device_name', 'sw-real-01')->firstOrFail();
    expect($created->Template()->where('templates.id', $this->template->id)->exists())->toBeTrue();
    expect($created->Vendor()->where('vendors.id', $this->vendor->id)->exists())->toBeTrue();
    expect($created->Category()->where('categories.id', $this->category->id)->exists())->toBeTrue();
    expect($created->Tag()->where('tags.id', $this->tag->id)->exists())->toBeTrue();

    File::delete($file);
});

test('solarwinds import skips duplicate device', function () {
    Device::factory()->create([
        'device_name' => 'sw-real-01',
        'device_ip' => '10.60.70.80',
    ]);

    $file = $this->tempDir . '/dup_device.json';
    File::put($file, json_encode([solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    expect(Device::where('device_name', 'sw-real-01')->count())->toEqual(1);

    File::delete($file);
});

test('solarwinds import rejects invalid ip', function () {
    $file = $this->tempDir . '/bad_ip.json';
    File::put($file, json_encode([solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_ip' => 'not-an-ip'])]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

    File::delete($file);
});

test('solarwinds import rejects nonexistent template', function () {
    $file = $this->tempDir . '/bad_template.json';
    File::put($file, json_encode([solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['template_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

    File::delete($file);
});

test('solarwinds import rejects nonexistent credential', function () {
    $file = $this->tempDir . '/bad_cred.json';
    File::put($file, json_encode([solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_cred_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

    File::delete($file);
});

test('solarwinds import rejects missing prompts', function () {
    $device = solarwindsValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag);
    unset($device['prompts']);

    $file = $this->tempDir . '/no_prompts.json';
    File::put($file, json_encode([$device]));

    $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

    File::delete($file);
});

test('solarwinds load devices fails without connection file', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:solarwinds-load-devices')->run();
    expect($exitCode)->toEqual(1);
});

test('solarwinds load devices fails without mappings file', function () {
    File::put($this->connectionFile, json_encode([
        'swis_url' => 'https://swis.test',
        'username' => 'admin',
        'password' => null,
        'password_encrypted' => false,
        'verify_ssl' => false,
        'timeout' => 30,
        'filters' => [],
    ], JSON_PRETTY_PRINT));

    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $exitCode = $this->artisan('rconfig:solarwinds-load-devices')->run();
    expect($exitCode)->toEqual(1);
});

test('solarwinds connection set url updates existing connection', function () {
    File::put($this->connectionFile, json_encode([
        'swis_url' => 'https://old.test',
        'username' => 'admin',
        'filters' => [],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--set-url' => 'https://new.test/'])->run();
    expect($exitCode)->toEqual(0);

    $config = json_decode(File::get($this->connectionFile), true);
    expect($config['swis_url'])->toEqual('https://new.test');
});

test('solarwinds connection set url fails without connection', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--set-url' => 'https://new.test'])->run();
    expect($exitCode)->toEqual(1);
});

test('solarwinds connection show with existing connection', function () {
    File::put($this->connectionFile, json_encode([
        'swis_url' => 'https://swis.test',
        'username' => 'admin',
        'password' => null,
        'password_encrypted' => false,
        'verify_ssl' => false,
        'timeout' => 30,
        'connection_status' => 'untested',
        'last_tested' => null,
        'solarwinds_version' => null,
        'node_count' => null,
        'filters' => [
            'include_groups' => [],
            'exclude_groups' => [],
            'include_machine_types' => [],
            'exclude_machine_types' => [],
            'include_statuses' => [1],
            'custom_property_filters' => [],
        ],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--show' => true])->run();
    expect($exitCode)->toEqual(0);
});

test('solarwinds connection show fails without connection', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--show' => true])->run();
    expect($exitCode)->toEqual(1);
});

test('solarwinds connection clear removes file', function () {
    File::put($this->connectionFile, json_encode(['swis_url' => 'https://swis.test'], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--clear' => true])
        ->expectsConfirmation('Are you sure you want to clear the SolarWinds connection configuration?', 'yes')
        ->run();

    expect($exitCode)->toEqual(0);
    $this->assertFileDoesNotExist($this->connectionFile);
});

test('solarwinds connection clear cancelled keeps file', function () {
    File::put($this->connectionFile, json_encode(['swis_url' => 'https://swis.test'], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--clear' => true])
        ->expectsConfirmation('Are you sure you want to clear the SolarWinds connection configuration?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);
    expect($this->connectionFile)->toBeFile();
});

test('solarwinds mappings delete removes entry', function () {
    File::put($this->mappingsFile, json_encode([
        'Cisco IOS' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
        'Juniper JUNOS' => ['device_type' => 'junos', 'template_id' => $this->template->id],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:solarwinds-device-mappings', ['--delete' => 'Cisco IOS'])
        ->expectsConfirmation("Are you sure you want to delete the mapping for 'Cisco IOS'?", 'yes')
        ->run();

    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    $this->assertArrayNotHasKey('Cisco IOS', $loaded);
    expect($loaded)->toHaveKey('Juniper JUNOS');
});

afterEach(function () {
    $filesToDelete = [
        $this->connectionFile,
        $this->mappingsFile,
        storage_path('app/rconfig/solarwinds_connection.stub.json'),
    ];

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
