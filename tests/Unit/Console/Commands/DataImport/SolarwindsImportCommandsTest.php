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

class SolarwindsImportCommandsTest extends TestCase
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
    }

    /** @test */
    public function test_solarwinds_connection_command_exists()
    {
        $exitCode = $this->artisan('rconfig:solarwinds-connection --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_solarwinds_connection_creates_stub_file()
    {
        $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');

        $this->artisan('rconfig:solarwinds-connection --info')->run();

        $this->assertFileExists($stubFile);

        $stub = json_decode(File::get($stubFile), true);
        $this->assertArrayHasKey('swis_url', $stub);
        $this->assertArrayHasKey('username', $stub);
        $this->assertArrayHasKey('filters', $stub);
    }

    /** @test */
    public function test_solarwinds_device_mappings_command_exists()
    {
        $exitCode = $this->artisan('rconfig:solarwinds-device-mappings --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_solarwinds_device_mappings_creates_empty_file()
    {
        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $this->artisan('rconfig:solarwinds-device-mappings --list')->run();

        $this->assertFileExists($this->mappingsFile);

        $content = json_decode(File::get($this->mappingsFile), true);
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    /** @test */
    public function test_solarwinds_device_mappings_can_list_existing()
    {
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
        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayHasKey('Cisco IOS', $loaded);
    }

    /** @test */
    public function test_solarwinds_load_devices_command_exists()
    {
        $exitCode = $this->artisan('rconfig:solarwinds-load-devices --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_solarwinds_import_devices_fails_with_missing_file()
    {
        $nonExistentFile = $this->tempDir . '/does_not_exist.json';

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $nonExistentFile])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_solarwinds_import_devices_fails_with_invalid_json()
    {
        $invalidJsonFile = $this->tempDir . '/invalid.json';
        File::put($invalidJsonFile, 'this is not valid json');

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $invalidJsonFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($invalidJsonFile);
    }

    /** @test */
    public function test_solarwinds_import_devices_fails_with_empty_array()
    {
        $emptyFile = $this->tempDir . '/empty.json';
        File::put($emptyFile, json_encode([]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $emptyFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($emptyFile);
    }

    /** @test */
    public function test_solarwinds_import_devices_dry_run_with_valid_device()
    {
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

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseMissing('devices', [
            'device_name' => 'test-switch-01',
        ]);

        File::delete($validFile);
    }

    /** @test */
    public function test_solarwinds_workflow_file_dependencies()
    {
        $this->artisan('rconfig:solarwinds-connection --info')->run();
        $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
        $this->assertFileExists($stubFile);

        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $this->artisan('rconfig:solarwinds-device-mappings --list')->run();
        $this->assertFileExists($this->mappingsFile);

        $mappings = json_decode(File::get($this->mappingsFile), true);
        $this->assertIsArray($mappings);
    }

    /** @test */
    public function test_solarwinds_connection_stub_has_proper_structure()
    {
        $this->artisan('rconfig:solarwinds-connection --info')->run();

        $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
        $stub = json_decode(File::get($stubFile), true);

        $this->assertArrayHasKey('swis_url', $stub);
        $this->assertArrayHasKey('username', $stub);
        $this->assertArrayHasKey('verify_ssl', $stub);
        $this->assertArrayHasKey('filters', $stub);
        $this->assertArrayHasKey('_comment', $stub);
        $this->assertArrayHasKey('_instructions', $stub);
    }

    /** @test */
    public function test_solarwinds_mappings_preserves_structure()
    {
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

        $this->assertArrayHasKey('Cisco IOS', $loaded);
        $this->assertEquals($this->template->id, $loaded['Cisco IOS']['template_id']);
        $this->assertEquals($this->credential->id, $loaded['Cisco IOS']['credential_id']);
        $this->assertArrayHasKey('custom_property_tag_mapping', $loaded['Cisco IOS']);
        $this->assertEquals(10, $loaded['Cisco IOS']['custom_property_tag_mapping']['Location']);
        $this->assertArrayHasKey('node_group_mapping', $loaded['Cisco IOS']);
        $this->assertEquals(1, $loaded['Cisco IOS']['node_group_mapping']['Core Routers']);
    }

    /** @test */
    public function test_solarwinds_json_structure_is_correct()
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
            'solarwinds_machine_type' => 'Cisco IOS',
            'solarwinds_node_groups' => ['Core'],
            'solarwinds_custom_properties' => ['Location' => 'DC1'],
        ];

        $file = $this->tempDir . '/structure_test.json';
        File::put($file, json_encode([$device]));

        $loaded = json_decode(File::get($file), true);

        $this->assertIsArray($loaded);
        $this->assertCount(1, $loaded);
        $this->assertEquals('test-device', $loaded[0]['device_name']);
        $this->assertEquals('Cisco IOS', $loaded[0]['solarwinds_machine_type']);
        $this->assertArrayHasKey('solarwinds_node_groups', $loaded[0]);
        $this->assertArrayHasKey('solarwinds_custom_properties', $loaded[0]);

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_mappings_file_location_is_correct()
    {
        $this->artisan('rconfig:solarwinds-device-mappings --list')->run();

        $this->assertFileExists($this->mappingsFile);
        $this->assertEquals(
            storage_path('app/rconfig/solarwinds_mappings.json'),
            $this->mappingsFile
        );
    }

    /** @test */
    public function test_solarwinds_connection_filters_structure()
    {
        $this->artisan('rconfig:solarwinds-connection --info')->run();

        $stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
        $stub = json_decode(File::get($stubFile), true);

        $this->assertArrayHasKey('filters', $stub);
        $this->assertArrayHasKey('include_groups', $stub['filters']);
        $this->assertArrayHasKey('exclude_groups', $stub['filters']);
        $this->assertArrayHasKey('include_machine_types', $stub['filters']);
        $this->assertArrayHasKey('exclude_machine_types', $stub['filters']);
        $this->assertArrayHasKey('include_statuses', $stub['filters']);
        $this->assertArrayHasKey('custom_property_filters', $stub['filters']);
    }

    /** @test */
    public function test_solarwinds_custom_property_mapping_in_output()
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

        $this->assertArrayHasKey('solarwinds_custom_properties', $loaded[0]);
        $this->assertEquals('DC-East', $loaded[0]['solarwinds_custom_properties']['Location']);
        $this->assertContains(10, $loaded[0]['tags']);
        $this->assertContains(15, $loaded[0]['tags']);

        File::delete($file);
    }

    /**
     * Build a fully valid device payload for the import command.
     *
     * @return array<string, mixed>
     */
    protected function validImportDevice(array $overrides = []): array
    {
        return array_merge([
            'device_name' => 'sw-real-01',
            'device_ip' => '10.60.70.80',
            'device_model' => 'Cisco IOS',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'sw-real-01>',
                'device_main_prompt' => 'sw-real-01#',
            ],
            'tags' => [$this->tag->id],
            'solarwinds_machine_type' => 'Cisco IOS',
        ], $overrides);
    }

    /** @test */
    public function test_solarwinds_import_creates_device_with_pivots()
    {
        $file = $this->tempDir . '/real_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Return to main menu?', 'no')
            ->run();

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('devices', [
            'device_name' => 'sw-real-01',
            'device_ip' => '10.60.70.80',
            'device_template' => $this->template->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'status' => 1,
        ]);

        $created = Device::where('device_name', 'sw-real-01')->firstOrFail();
        $this->assertTrue($created->Template()->where('templates.id', $this->template->id)->exists());
        $this->assertTrue($created->Vendor()->where('vendors.id', $this->vendor->id)->exists());
        $this->assertTrue($created->Category()->where('categories.id', $this->category->id)->exists());
        $this->assertTrue($created->Tag()->where('tags.id', $this->tag->id)->exists());

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_import_skips_duplicate_device()
    {
        Device::factory()->create([
            'device_name' => 'sw-real-01',
            'device_ip' => '10.60.70.80',
        ]);

        $file = $this->tempDir . '/dup_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertEquals(1, Device::where('device_name', 'sw-real-01')->count());

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_import_rejects_invalid_ip()
    {
        $file = $this->tempDir . '/bad_ip.json';
        File::put($file, json_encode([$this->validImportDevice(['device_ip' => 'not-an-ip'])]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_import_rejects_nonexistent_template()
    {
        $file = $this->tempDir . '/bad_template.json';
        File::put($file, json_encode([$this->validImportDevice(['template_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_import_rejects_nonexistent_credential()
    {
        $file = $this->tempDir . '/bad_cred.json';
        File::put($file, json_encode([$this->validImportDevice(['device_cred_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_import_rejects_missing_prompts()
    {
        $device = $this->validImportDevice();
        unset($device['prompts']);

        $file = $this->tempDir . '/no_prompts.json';
        File::put($file, json_encode([$device]));

        $exitCode = $this->artisan('rconfig:solarwinds-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'sw-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_solarwinds_load_devices_fails_without_connection_file()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:solarwinds-load-devices')->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_solarwinds_load_devices_fails_without_mappings_file()
    {
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
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_solarwinds_connection_set_url_updates_existing_connection()
    {
        File::put($this->connectionFile, json_encode([
            'swis_url' => 'https://old.test',
            'username' => 'admin',
            'filters' => [],
        ], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--set-url' => 'https://new.test/'])->run();
        $this->assertEquals(0, $exitCode);

        $config = json_decode(File::get($this->connectionFile), true);
        $this->assertEquals('https://new.test', $config['swis_url']);
    }

    /** @test */
    public function test_solarwinds_connection_set_url_fails_without_connection()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--set-url' => 'https://new.test'])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_solarwinds_connection_show_with_existing_connection()
    {
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
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_solarwinds_connection_show_fails_without_connection()
    {
        if (File::exists($this->connectionFile)) {
            File::delete($this->connectionFile);
        }

        $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--show' => true])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_solarwinds_connection_clear_removes_file()
    {
        File::put($this->connectionFile, json_encode(['swis_url' => 'https://swis.test'], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--clear' => true])
            ->expectsConfirmation('Are you sure you want to clear the SolarWinds connection configuration?', 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);
        $this->assertFileDoesNotExist($this->connectionFile);
    }

    /** @test */
    public function test_solarwinds_connection_clear_cancelled_keeps_file()
    {
        File::put($this->connectionFile, json_encode(['swis_url' => 'https://swis.test'], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:solarwinds-connection', ['--clear' => true])
            ->expectsConfirmation('Are you sure you want to clear the SolarWinds connection configuration?', 'no')
            ->run();

        $this->assertEquals(0, $exitCode);
        $this->assertFileExists($this->connectionFile);
    }

    /** @test */
    public function test_solarwinds_mappings_delete_removes_entry()
    {
        File::put($this->mappingsFile, json_encode([
            'Cisco IOS' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
            'Juniper JUNOS' => ['device_type' => 'junos', 'template_id' => $this->template->id],
        ], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:solarwinds-device-mappings', ['--delete' => 'Cisco IOS'])
            ->expectsConfirmation("Are you sure you want to delete the mapping for 'Cisco IOS'?", 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayNotHasKey('Cisco IOS', $loaded);
        $this->assertArrayHasKey('Juniper JUNOS', $loaded);
    }

    public function tearDown(): void
    {
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

        parent::tearDown();
    }
}
