<?php

namespace Tests\Unit\Console\Commands\DataImport;

use App\Models\Category;
use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NetMriImportCommandsTest extends TestCase
{
    /** @var User */
    protected $user;

    protected $connectionFile;
    protected $mappingsFile;
    protected $tempDir;
    protected $template;
    protected $vendor;
    protected $category;
    protected $credential;
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();
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
    }

    /** @test */
    public function test_netmri_connection_command_exists()
    {
        $exitCode = $this->artisan('rconfig:netmri-connection --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_netmri_connection_creates_stub_file()
    {
        $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');

        $this->artisan('rconfig:netmri-connection --info')->run();

        $this->assertFileExists($stubFile);

        $stub = json_decode(File::get($stubFile), true);
        $this->assertArrayHasKey('api_url', $stub);
        $this->assertArrayHasKey('auth_type', $stub);
        $this->assertArrayHasKey('filters', $stub);
    }

    /** @test */
    public function test_netmri_device_mappings_command_exists()
    {
        $exitCode = $this->artisan('rconfig:netmri-device-mappings --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_netmri_device_mappings_creates_empty_file()
    {
        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $this->artisan('rconfig:netmri-device-mappings --list')->run();

        $this->assertFileExists($this->mappingsFile);

        $content = json_decode(File::get($this->mappingsFile), true);
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    /** @test */
    public function test_netmri_device_mappings_can_list_existing()
    {
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
        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayHasKey('cisco-ios', $loaded);
    }

    /** @test */
    public function test_netmri_load_devices_command_exists()
    {
        $exitCode = $this->artisan('rconfig:netmri-load-devices --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_netmri_import_devices_fails_with_missing_file()
    {
        $nonExistentFile = $this->tempDir . '/does_not_exist.json';

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $nonExistentFile])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_netmri_import_devices_fails_with_invalid_json()
    {
        $invalidJsonFile = $this->tempDir . '/invalid.json';
        File::put($invalidJsonFile, 'this is not valid json');

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $invalidJsonFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($invalidJsonFile);
    }

    /** @test */
    public function test_netmri_import_devices_fails_with_empty_array()
    {
        $emptyFile = $this->tempDir . '/empty.json';
        File::put($emptyFile, json_encode([]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $emptyFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($emptyFile);
    }

    /** @test */
    public function test_netmri_import_devices_dry_run_with_valid_device()
    {
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

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseMissing('devices', [
            'device_name' => 'test-router-01',
        ]);

        File::delete($validFile);
    }

    /** @test */
    public function test_netmri_workflow_file_dependencies()
    {
        $this->artisan('rconfig:netmri-connection --info')->run();
        $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');
        $this->assertFileExists($stubFile);

        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $this->artisan('rconfig:netmri-device-mappings --list')->run();
        $this->assertFileExists($this->mappingsFile);

        $mappings = json_decode(File::get($this->mappingsFile), true);
        $this->assertIsArray($mappings);
    }

    /** @test */
    public function test_netmri_connection_stub_has_proper_structure()
    {
        $this->artisan('rconfig:netmri-connection --info')->run();

        $stubFile = storage_path('app/rconfig/netmri_connection.stub.json');
        $stub = json_decode(File::get($stubFile), true);

        $this->assertArrayHasKey('api_url', $stub);
        $this->assertArrayHasKey('api_version', $stub);
        $this->assertArrayHasKey('auth_type', $stub);
        $this->assertArrayHasKey('filters', $stub);
        $this->assertArrayHasKey('_comment', $stub);
        $this->assertArrayHasKey('_instructions', $stub);
    }

    /** @test */
    public function test_netmri_mappings_preserves_structure()
    {
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

        $this->assertArrayHasKey('cisco-ios', $loaded);
        $this->assertEquals($this->template->id, $loaded['cisco-ios']['template_id']);
        $this->assertEquals($this->credential->id, $loaded['cisco-ios']['credential_id']);
        $this->assertArrayHasKey('site_zone_tag_mapping', $loaded['cisco-ios']);
        $this->assertEquals(10, $loaded['cisco-ios']['site_zone_tag_mapping']['DC-East']);
    }

    /** @test */
    public function test_netmri_json_structure_is_correct()
    {
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

        $this->assertIsArray($loaded);
        $this->assertCount(1, $loaded);
        $this->assertEquals('test-device', $loaded[0]['device_name']);
        $this->assertEquals('DC-East', $loaded[0]['netmri_site']);
        $this->assertEquals('Core', $loaded[0]['netmri_zone']);

        File::delete($file);
    }

    /** @test */
    public function test_netmri_mappings_file_location_is_correct()
    {
        $this->artisan('rconfig:netmri-device-mappings --list')->run();

        $this->assertFileExists($this->mappingsFile);
        $this->assertEquals(
            storage_path('app/rconfig/netmri_mappings.json'),
            $this->mappingsFile
        );
    }

    /**
     * Build a fully valid device payload for the import command.
     *
     * @return array<string, mixed>
     */
    protected function validImportDevice(array $overrides = []): array
    {
        return array_merge([
            'device_name' => 'netmri-real-01',
            'device_ip' => '10.80.90.10',
            'device_model' => 'Cisco 7606',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'netmri-real-01>',
                'device_main_prompt' => 'netmri-real-01#',
            ],
            'tags' => [$this->tag->id],
            'netmri_device_type' => 'cisco-ios',
        ], $overrides);
    }

    /** @test */
    public function test_netmri_import_creates_device_with_pivots()
    {
        $file = $this->tempDir . '/real_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Return to main menu?', 'no')
            ->run();

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('devices', [
            'device_name' => 'netmri-real-01',
            'device_ip' => '10.80.90.10',
            'device_template' => $this->template->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'status' => 1,
        ]);

        $created = Device::where('device_name', 'netmri-real-01')->firstOrFail();
        $this->assertTrue($created->Template()->where('templates.id', $this->template->id)->exists());
        $this->assertTrue($created->Vendor()->where('vendors.id', $this->vendor->id)->exists());
        $this->assertTrue($created->Category()->where('categories.id', $this->category->id)->exists());
        $this->assertTrue($created->Tag()->where('tags.id', $this->tag->id)->exists());

        File::delete($file);
    }

    /** @test */
    public function test_netmri_import_skips_duplicate_device()
    {
        Device::factory()->create([
            'device_name' => 'netmri-real-01',
            'device_ip' => '10.80.90.10',
        ]);

        $file = $this->tempDir . '/dup_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertEquals(1, Device::where('device_name', 'netmri-real-01')->count());

        File::delete($file);
    }

    /** @test */
    public function test_netmri_import_rejects_invalid_ip()
    {
        $file = $this->tempDir . '/bad_ip.json';
        File::put($file, json_encode([$this->validImportDevice(['device_ip' => 'not-an-ip'])]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_netmri_import_rejects_nonexistent_template()
    {
        $file = $this->tempDir . '/bad_template.json';
        File::put($file, json_encode([$this->validImportDevice(['template_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_netmri_import_rejects_nonexistent_credential()
    {
        $file = $this->tempDir . '/bad_cred.json';
        File::put($file, json_encode([$this->validImportDevice(['device_cred_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_netmri_import_rejects_missing_prompts()
    {
        $device = $this->validImportDevice();
        unset($device['prompts']);

        $file = $this->tempDir . '/no_prompts.json';
        File::put($file, json_encode([$device]));

        $exitCode = $this->artisan('rconfig:netmri-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'netmri-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_netmri_load_devices_fails_without_connection_file()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:netmri-load-devices')->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_netmri_load_devices_fails_without_mappings_file()
    {
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
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_netmri_connection_set_url_updates_existing_connection()
    {
        File::put($this->connectionFile, json_encode([
            'api_url' => 'https://old.test',
            'username' => 'admin',
            'filters' => [],
        ], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:netmri-connection', ['--set-url' => 'https://new.test/'])->run();
        $this->assertEquals(0, $exitCode);

        $config = json_decode(File::get($this->connectionFile), true);
        $this->assertEquals('https://new.test', $config['api_url']);
    }

    /** @test */
    public function test_netmri_connection_set_url_fails_without_connection()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:netmri-connection', ['--set-url' => 'https://new.test'])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_netmri_connection_show_with_existing_connection()
    {
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
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_netmri_connection_show_fails_without_connection()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:netmri-connection', ['--show' => true])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_netmri_connection_clear_removes_file()
    {
        File::put($this->connectionFile, json_encode(['api_url' => 'https://netmri.test'], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:netmri-connection', ['--clear' => true])
            ->expectsConfirmation('Are you sure you want to clear the NetMRI connection configuration?', 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);
        $this->assertFileDoesNotExist($this->connectionFile);
    }

    /** @test */
    public function test_netmri_connection_clear_cancelled_keeps_file()
    {
        File::put($this->connectionFile, json_encode(['api_url' => 'https://netmri.test'], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:netmri-connection', ['--clear' => true])
            ->expectsConfirmation('Are you sure you want to clear the NetMRI connection configuration?', 'no')
            ->run();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->connectionFile);
    }

    /** @test */
    public function test_netmri_mappings_delete_removes_entry()
    {
        File::put($this->mappingsFile, json_encode([
            'cisco-ios' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
            'juniper-junos' => ['device_type' => 'junos', 'template_id' => $this->template->id],
        ], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:netmri-device-mappings', ['--delete' => 'cisco-ios'])
            ->expectsConfirmation("Are you sure you want to delete the mapping for 'cisco-ios'?", 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayNotHasKey('cisco-ios', $loaded);
        $this->assertArrayHasKey('juniper-junos', $loaded);
    }

    public function tearDown(): void
    {
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

        parent::tearDown();
    }
}
