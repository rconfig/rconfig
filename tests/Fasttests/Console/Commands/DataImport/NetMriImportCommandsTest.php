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

    $this->connectionFile = storage_path('app/rconfig/netmri_connection.json');
    $this->mappingsFile = storage_path('app/rconfig/netmri_mappings.json');
    $this->tempDir = storage_path('app/rconfig/tempdir');

    $dir = storage_path('app/rconfig');
    if (! File::exists($dir)) {
        File::makeDirectory($dir, 0755, true);
    }

    if (! File::exists($this->tempDir)) {
        File::makeDirectory($this->tempDir, 0755, true);
    }

    $this->template = Template::factory()->create(['templateName' => 'netmri_test_template']);
    $this->vendor = Vendor::factory()->create(['vendorName' => 'netmri_test_vendor']);
    $this->category = Category::factory()->create(['categoryName' => 'netmri_test_category']);
    $this->credential = DeviceCredentials::factory()->create([
        'cred_name' => 'netmri_test_cred',
        'cred_description' => 'NetMRI Test Credentials',
    ]);
    $this->tag = Tag::factory()->create(['tagname' => 'netmri_test_tag']);
});

test('netmri connection command exists', function () {
    $exitCode = $this->artisan('rconfig:netmri-connection --info')->run();
    expect($exitCode)->toEqual(0);
});

test('netmri connection creates stub file', function () {
    $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');

    $this->artisan('rconfig:netmri-connection --info')->run();

    expect($stubFile)->toBeFile();

    $stub = json_decode(File::get($stubFile), true);
    expect($stub)->toHaveKey('api_url');
    expect($stub)->toHaveKey('auth_type');
    expect($stub)->toHaveKey('filters');
});

test('netmri device mappings command exists', function () {
    $exitCode = $this->artisan('rconfig:netmri-device-mappings --info')->run();
    expect($exitCode)->toEqual(0);
});

test('netmri device mappings creates empty file', function () {
    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:netmri-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();

    $content = json_decode(File::get($this->mappingsFile), true);
    expect($content)->toBeArray();
    expect($content)->toBeEmpty();
});

test('netmri device mappings can list existing', function () {
    $mappings = [
        'cisco-ios' => [
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
            'site_zone_tag_mapping' => [],
        ],
    ];

    File::put($this->mappingsFile, json_encode($mappings, JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-device-mappings --list')->run();
    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    expect($loaded)->toHaveKey('cisco-ios');
});

test('netmri load devices command exists', function () {
    $exitCode = $this->artisan('rconfig:netmri-load-devices --info')->run();
    expect($exitCode)->toEqual(0);
});

test('netmri import devices fails with missing file', function () {
    $nonExistentFile = $this->tempDir . '/does_not_exist.json';

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $nonExistentFile])->run();
    expect($exitCode)->toEqual(1);
});

test('netmri import devices fails with invalid json', function () {
    $invalidJsonFile = $this->tempDir . '/invalid.json';
    File::put($invalidJsonFile, 'this is not valid json');

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $invalidJsonFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($invalidJsonFile);
});

test('netmri import devices fails with empty array', function () {
    $emptyFile = $this->tempDir . '/empty.json';
    File::put($emptyFile, json_encode([]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $emptyFile])->run();
    expect($exitCode)->toEqual(1);

    File::delete($emptyFile);
});

test('netmri import devices dry run with valid device', function () {
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
            'netmri_device_type' => 'cisco-ios',
            'netmri_site' => 'DC-East',
            'netmri_zone' => 'Core',
            'connection_type' => 'ssh',
            'port' => 22,
        ],
    ];

    $validFile = $this->tempDir . '/valid_device.json';
    File::put($validFile, json_encode($validDevice));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', [
        'file' => $validFile,
        '--dry-run' => true,
    ])->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseMissing('devices', [
        'device_name' => 'test-router-01',
    ]);

    File::delete($validFile);
});

test('netmri workflow file dependencies', function () {
    $this->artisan('rconfig:netmri-connection --info')->run();
    $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');
    expect($stubFile)->toBeFile();

    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $this->artisan('rconfig:netmri-device-mappings --list')->run();
    expect($this->mappingsFile)->toBeFile();

    $mappings = json_decode(File::get($this->mappingsFile), true);
    expect($mappings)->toBeArray();
});

test('netmri connection stub has proper structure', function () {
    $this->artisan('rconfig:netmri-connection --info')->run();

    $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');
    $stub = json_decode(File::get($stubFile), true);

    expect($stub)->toHaveKey('api_url');
    expect($stub)->toHaveKey('api_version');
    expect($stub)->toHaveKey('auth_type');
    expect($stub)->toHaveKey('filters');
    expect($stub)->toHaveKey('_comment');
    expect($stub)->toHaveKey('_instructions');
});

test('netmri mappings preserves structure', function () {
    $mapping = [
        'cisco-ios' => [
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
            'site_zone_tag_mapping' => [
                'DC-East' => 10,
                'DMZ' => 20,
            ],
        ],
    ];

    File::put($this->mappingsFile, json_encode($mapping, JSON_PRETTY_PRINT));

    $loaded = json_decode(File::get($this->mappingsFile), true);

    expect($loaded)->toHaveKey('cisco-ios');
    expect($loaded['cisco-ios']['template_id'])->toEqual($this->template->id);
    expect($loaded['cisco-ios']['credential_id'])->toEqual($this->credential->id);
    expect($loaded['cisco-ios'])->toHaveKey('site_zone_tag_mapping');
    expect($loaded['cisco-ios']['site_zone_tag_mapping']['DC-East'])->toEqual(10);
});

test('netmri json structure is correct', function () {
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
        'netmri_device_type' => 'cisco-ios',
        'netmri_site' => 'DC-East',
        'netmri_zone' => 'Core',
    ];

    $file = $this->tempDir . '/structure_test.json';
    File::put($file, json_encode([$device]));

    $loaded = json_decode(File::get($file), true);

    expect($loaded)->toBeArray();
    expect($loaded)->toHaveCount(1);
    expect($loaded[0]['device_name'])->toEqual('test-device');
    expect($loaded[0]['netmri_site'])->toEqual('DC-East');
    expect($loaded[0]['netmri_zone'])->toEqual('Core');

    File::delete($file);
});

test('netmri mappings file location is correct', function () {
    $this->artisan('rconfig:netmri-device-mappings --list')->run();

    expect($this->mappingsFile)->toBeFile();
    expect($this->mappingsFile)->toEqual(storage_path('app/rconfig/netmri_mappings.json'));
});

/**
 * Build a fully valid device payload for the import command.
 *
 * @return array<string, mixed>
 */
function netmriValidImportDevice(Template $template, Vendor $vendor, Category $category, DeviceCredentials $credential, Tag $tag, array $overrides = []): array
{
    return array_merge([
        'device_name' => 'netmri-real-01',
        'device_ip' => '10.80.90.10',
        'device_model' => 'Cisco 7606',
        'template_id' => $template->id,
        'vendor_id' => $vendor->id,
        'device_category_id' => $category->id,
        'device_cred_id' => $credential->id,
        'prompts' => [
            'device_enable_prompt' => 'netmri-real-01>',
            'device_main_prompt' => 'netmri-real-01#',
        ],
        'tags' => [$tag->id],
        'netmri_device_type' => 'cisco-ios',
    ], $overrides);
}

test('netmri import creates device with pivots', function () {
    $file = $this->tempDir . '/real_device.json';
    File::put($file, json_encode([netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Return to main menu?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);

    $this->assertDatabaseHas('devices', [
        'device_name' => 'netmri-real-01',
        'device_ip' => '10.80.90.10',
        'device_template' => $this->template->id,
        'device_category_id' => $this->category->id,
        'device_cred_id' => $this->credential->id,
        'status' => 1,
    ]);

    $created = Device::where('device_name', 'netmri-real-01')->firstOrFail();
    expect($created->Template()->where('templates.id', $this->template->id)->exists())->toBeTrue();
    expect($created->Vendor()->where('vendors.id', $this->vendor->id)->exists())->toBeTrue();
    expect($created->Category()->where('categories.id', $this->category->id)->exists())->toBeTrue();
    expect($created->Tag()->where('tags.id', $this->tag->id)->exists())->toBeTrue();

    File::delete($file);
});

test('netmri import skips duplicate device', function () {
    Device::factory()->create([
        'device_name' => 'netmri-real-01',
        'device_ip' => '10.80.90.10',
    ]);

    $file = $this->tempDir . '/dup_device.json';
    File::put($file, json_encode([netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag)]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    expect(Device::where('device_name', 'netmri-real-01')->count())->toEqual(1);

    File::delete($file);
});

test('netmri import rejects invalid ip', function () {
    $file = $this->tempDir . '/bad_ip.json';
    File::put($file, json_encode([netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_ip' => 'not-an-ip'])]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

    File::delete($file);
});

test('netmri import rejects nonexistent template', function () {
    $file = $this->tempDir . '/bad_template.json';
    File::put($file, json_encode([netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['template_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

    File::delete($file);
});

test('netmri import rejects nonexistent credential', function () {
    $file = $this->tempDir . '/bad_cred.json';
    File::put($file, json_encode([netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag, ['device_cred_id' => 999999])]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

    File::delete($file);
});

test('netmri import rejects missing prompts', function () {
    $device = netmriValidImportDevice($this->template, $this->vendor, $this->category, $this->credential, $this->tag);
    unset($device['prompts']);

    $file = $this->tempDir . '/no_prompts.json';
    File::put($file, json_encode([$device]));

    $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
        ->expectsConfirmation('Continue with 0 valid devices?', 'no')
        ->run();

    expect($exitCode)->toEqual(1);
    $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

    File::delete($file);
});

test('netmri load devices fails without connection file', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:netmri-load-devices')->run();
    expect($exitCode)->toEqual(1);
});

test('netmri load devices fails without mappings file', function () {
    File::put($this->connectionFile, json_encode([
        'api_url' => 'https://netmri.test',
        'api_version' => '3.3',
        'auth_type' => 'basic',
        'username' => 'admin',
        'password' => null,
        'password_encrypted' => false,
        'api_token' => null,
        'verify_ssl' => false,
        'timeout' => 30,
        'filters' => [],
    ], JSON_PRETTY_PRINT));

    if (File::exists($this->mappingsFile)) {
        File::delete($this->mappingsFile);
    }

    $exitCode = $this->artisan('rconfig:netmri-load-devices')->run();
    expect($exitCode)->toEqual(1);
});

test('netmri connection set url updates existing connection', function () {
    File::put($this->connectionFile, json_encode([
        'api_url' => 'https://old.test',
        'username' => 'admin',
        'filters' => [],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--set-url' => 'https://new.test/'])->run();
    expect($exitCode)->toEqual(0);

    $config = json_decode(File::get($this->connectionFile), true);
    expect($config['api_url'])->toEqual('https://new.test');
});

test('netmri connection set url fails without connection', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--set-url' => 'https://new.test'])->run();
    expect($exitCode)->toEqual(1);
});

test('netmri connection show with existing connection', function () {
    File::put($this->connectionFile, json_encode([
        'api_url' => 'https://netmri.test',
        'api_version' => '3.3',
        'auth_type' => 'basic',
        'username' => 'admin',
        'password' => null,
        'password_encrypted' => false,
        'api_token' => null,
        'verify_ssl' => false,
        'timeout' => 30,
        'connection_status' => 'untested',
        'last_tested' => null,
        'netmri_version' => null,
        'device_count' => null,
        'filters' => [
            'include_sites' => [],
            'exclude_sites' => [],
            'include_zones' => [],
            'exclude_zones' => [],
            'include_device_types' => [],
            'exclude_device_types' => [],
            'only_managed' => true,
        ],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--show' => true])->run();
    expect($exitCode)->toEqual(0);
});

test('netmri connection show fails without connection', function () {
    if (File::exists($this->connectionFile)) {
        File::delete($this->connectionFile);
    }

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--show' => true])->run();
    expect($exitCode)->toEqual(1);
});

test('netmri connection clear removes file', function () {
    File::put($this->connectionFile, json_encode(['api_url' => 'https://netmri.test'], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--clear' => true])
        ->expectsConfirmation('Are you sure you want to clear the NetMRI connection configuration?', 'yes')
        ->run();

    expect($exitCode)->toEqual(0);
    $this->assertFileDoesNotExist($this->connectionFile);
});

test('netmri connection clear cancelled keeps file', function () {
    File::put($this->connectionFile, json_encode(['api_url' => 'https://netmri.test'], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-connection', ['--clear' => true])
        ->expectsConfirmation('Are you sure you want to clear the NetMRI connection configuration?', 'no')
        ->run();

    expect($exitCode)->toEqual(0);
    expect($this->connectionFile)->toBeFile();
});

test('netmri mappings delete removes entry', function () {
    File::put($this->mappingsFile, json_encode([
        'cisco-ios' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
        'juniper-junos' => ['device_type' => 'junos', 'template_id' => $this->template->id],
    ], JSON_PRETTY_PRINT));

    $exitCode = $this->artisan('rconfig:netmri-device-mappings', ['--delete' => 'cisco-ios'])
        ->expectsConfirmation("Are you sure you want to delete the mapping for 'cisco-ios'?", 'yes')
        ->run();

    expect($exitCode)->toEqual(0);

    $loaded = json_decode(File::get($this->mappingsFile), true);
    $this->assertArrayNotHasKey('cisco-ios', $loaded);
    expect($loaded)->toHaveKey('juniper-junos');
});

afterEach(function () {
    $filesToDelete = [
        $this->connectionFile,
        $this->mappingsFile,
        storage_path('app/rconfig/netmri_connection.stub.json'),
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
