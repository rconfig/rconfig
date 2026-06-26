<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

class ConfigCompareApiTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_show_change_by_current_config_id_returns_the_diff(): void
    {
        $previous = Config::factory()->create(['device_id' => 555001, 'command' => 'show run', 'download_status' => 1]);
        $current = Config::factory()->create(['device_id' => 555001, 'command' => 'show run', 'download_status' => 1]);

        $change = ConfigChange::create([
            'current_config_id' => $current->id,
            'previous_config_id' => $previous->id,
            'config_version' => 2,
            'config_change_type' => 'changed',
            'config_diff' => '<div class="diff-wrapper">diff body</div>',
            'change_trigger' => 'pull',
        ]);

        $response = $this->getJson('/api/config-changes/current-config/' . $current->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $change->id,
                'config_change_type' => 'changed',
                'config_version' => 2,
            ]);
    }

    public function test_config_history_lists_versions_for_device_and_command(): void
    {
        Config::factory()->create(['device_id' => 555002, 'command' => 'show run', 'download_status' => 1, 'config_version' => 1, 'latest_version' => 0]);
        Config::factory()->create(['device_id' => 555002, 'command' => 'show run', 'download_status' => 1, 'config_version' => 2, 'latest_version' => 1]);

        $response = $this->getJson('/api/configs/config-history/555002/show run?perPage=10');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_compare_options_can_be_read_updated_and_reset(): void
    {
        $read = $this->getJson('/api/settings/compare-options');
        $read->assertStatus(200);

        $update = $this->patchJson('/api/settings/compare-options/1', [
            'context' => 5,
            'lengthLimit' => 30000,
            'ignoreCase' => true,
            'ignoreLineEnding' => false,
            'ignoreWhitespace' => true,
            'config_compare_exclusion_file' => "// test\n#[global]\n/^foo.*$/m",
        ]);
        $update->assertStatus(200);

        $setting = Setting::find(1);
        $this->assertSame(5, $setting->config_compare_settings['context']);
        $this->assertTrue($setting->config_compare_settings['ignoreWhitespace']);
        $this->assertStringContainsString('foo', $setting->config_compare_exclusion_file);

        $default = $this->getJson('/api/settings/compare-options/default-template');
        $default->assertStatus(200);
    }
}
