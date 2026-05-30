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

class OxidizedImportCommandsTest extends TestCase
{
    /** @var User */
    protected $user;

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
    }

    /** @test */
    public function test_oxidized_device_mappings_command_exists()
    {
        $exitCode = $this->artisan('rconfig:oxidized-device-mappings --info')->run();
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function test_oxidized_device_mappings_creates_empty_file()
    {
        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $this->artisan('rconfig:oxidized-device-mappings --list')->run();

        $this->assertFileExists($this->mappingsFile);

        $content = json_decode(File::get($this->mappingsFile), true);
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    /** @test */
    public function test_oxidized_device_mappings_can_list_existing()
    {
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
        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayHasKey('ios', $loaded);
    }

    /** @test */
    public function test_oxidized_import_devices_fails_with_missing_file()
    {
        $nonExistentFile = $this->tempDir . '/does_not_exist.json';

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $nonExistentFile])->run();
        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_oxidized_import_devices_fails_with_invalid_json()
    {
        $invalidJsonFile = $this->tempDir . '/invalid.json';
        File::put($invalidJsonFile, 'this is not valid json');

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $invalidJsonFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($invalidJsonFile);
    }

    /** @test */
    public function test_oxidized_import_devices_fails_with_empty_array()
    {
        $emptyFile = $this->tempDir . '/empty.json';
        File::put($emptyFile, json_encode([]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $emptyFile])->run();
        $this->assertEquals(1, $exitCode);

        File::delete($emptyFile);
    }

    /** @test */
    public function test_oxidized_import_devices_dry_run_mode()
    {
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

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseMissing('devices', [
            'device_name' => 'test-router-01',
        ]);

        File::delete($validFile);
    }

    /**
     * Build a fully valid device payload for the import command.
     *
     * @return array<string, mixed>
     */
    protected function validImportDevice(array $overrides = []): array
    {
        return array_merge([
            'device_name' => 'oxidized-real-01',
            'device_ip' => '10.40.50.60',
            'device_model' => 'cisco_ios',
            'template_id' => $this->template->id,
            'vendor_id' => $this->vendor->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'prompts' => [
                'device_enable_prompt' => 'oxidized-real-01>',
                'device_main_prompt' => 'oxidized-real-01#',
            ],
            'tags' => [$this->tag->id],
        ], $overrides);
    }

    /** @test */
    public function test_oxidized_import_creates_device_with_pivots()
    {
        $file = $this->tempDir . '/real_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Return to main menu?', 'no')
            ->run();

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('devices', [
            'device_name' => 'oxidized-real-01',
            'device_ip' => '10.40.50.60',
            'device_template' => $this->template->id,
            'device_category_id' => $this->category->id,
            'device_cred_id' => $this->credential->id,
            'status' => 1,
        ]);

        $created = Device::where('device_name', 'oxidized-real-01')->firstOrFail();
        $this->assertTrue($created->Template()->where('templates.id', $this->template->id)->exists());
        $this->assertTrue($created->Vendor()->where('vendors.id', $this->vendor->id)->exists());
        $this->assertTrue($created->Category()->where('categories.id', $this->category->id)->exists());
        $this->assertTrue($created->Tag()->where('tags.id', $this->tag->id)->exists());

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_skips_duplicate_device()
    {
        Device::factory()->create([
            'device_name' => 'oxidized-real-01',
            'device_ip' => '10.40.50.60',
        ]);

        $file = $this->tempDir . '/dup_device.json';
        File::put($file, json_encode([$this->validImportDevice()]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertEquals(1, Device::where('device_name', 'oxidized-real-01')->count());

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_rejects_invalid_ip()
    {
        $file = $this->tempDir . '/bad_ip.json';
        File::put($file, json_encode([$this->validImportDevice(['device_ip' => 'not-an-ip'])]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_rejects_nonexistent_template()
    {
        $file = $this->tempDir . '/bad_template.json';
        File::put($file, json_encode([$this->validImportDevice(['template_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_rejects_nonexistent_vendor()
    {
        $file = $this->tempDir . '/bad_vendor.json';
        File::put($file, json_encode([$this->validImportDevice(['vendor_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_rejects_nonexistent_category()
    {
        $file = $this->tempDir . '/bad_category.json';
        File::put($file, json_encode([$this->validImportDevice(['device_category_id' => 999999])]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_import_rejects_missing_prompts()
    {
        $device = $this->validImportDevice();
        unset($device['prompts']);

        $file = $this->tempDir . '/no_prompts.json';
        File::put($file, json_encode([$device]));

        $exitCode = $this->artisan('rconfig:oxidized-import-devices', ['file' => $file])
            ->expectsConfirmation('Continue with 0 valid devices?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseMissing('devices', ['device_name' => 'oxidized-real-01']);

        File::delete($file);
    }

    /** @test */
    public function test_oxidized_load_devices_fails_with_missing_input_file()
    {
        $exitCode = $this->artisan('rconfig:oxidized-load-devices', [
            'file' => $this->tempDir . '/nope_hosts',
        ])->run();

        $this->assertEquals(1, $exitCode);
    }

    /** @test */
    public function test_oxidized_load_devices_fails_without_mappings_file()
    {
        if (File::exists($this->mappingsFile)) {
            File::delete($this->mappingsFile);
        }

        $hostsFile = $this->tempDir . '/oxidized_hosts';
        File::put($hostsFile, "router1:ios\n");

        $exitCode = $this->artisan('rconfig:oxidized-load-devices', ['file' => $hostsFile])
            ->expectsConfirmation('Would you like to create device mappings now?', 'no')
            ->run();

        $this->assertEquals(1, $exitCode);

        File::delete($hostsFile);
    }

    /** @test */
    public function test_oxidized_mappings_delete_removes_entry()
    {
        File::put($this->mappingsFile, json_encode([
            'ios' => ['device_type' => 'cisco_ios', 'template_id' => $this->template->id],
            'junos' => ['device_type' => 'junos', 'template_id' => $this->template->id],
        ], JSON_PRETTY_PRINT));

        $exitCode = $this->artisan('rconfig:oxidized-device-mappings', ['--delete' => 'ios'])
            ->expectsConfirmation("Are you sure you want to delete the mapping for 'ios'?", 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);

        $loaded = json_decode(File::get($this->mappingsFile), true);
        $this->assertArrayNotHasKey('ios', $loaded);
        $this->assertArrayHasKey('junos', $loaded);
    }

    public function tearDown(): void
    {
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

        parent::tearDown();
    }
}
