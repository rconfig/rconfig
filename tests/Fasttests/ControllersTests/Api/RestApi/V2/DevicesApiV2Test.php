<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\V2;

use App\Jobs\DownloadConfigNowJob;
use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Queue;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class DevicesApiV2Test extends TestCase
{
    use TolerantOfTransientDbLocks;

    protected RestApiToken $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
        Queue::fake();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    /**
     * @return array<string, mixed>
     */
    private function validDevicePayload(): array
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory(1)->create();
        $commands = Command::factory(5)->create();
        $template = Template::factory(1)->create();
        $tags = Tag::factory(3)->create();

        foreach ($commands as $command) {
            DB::table('category_command')->insert([
                'command_id' => $command->id,
                'category_id' => $category->id,
            ]);
        }

        $device = Device::factory()->make([
            'device_category_id' => $category->id,
            'device_vendor' => $vendor->toArray(),
            'device_tags' => $tags,
            'device_template' => $template->toArray(),
        ]);

        return $device->toArray();
    }

    public function test_index_returns_paginated_devices(): void
    {
        Device::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/devices?perPage=100')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_device(): void
    {
        $device = Device::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/devices/' . $device->id)
            ->assertStatus(200)
            ->assertJsonFragment(['device_name' => $device->device_name]);
    }

    public function test_summary_returns_expected_keys(): void
    {
        Device::factory(4)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/devices/summary')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['total_devices', 'backup_success_last_run', 'backup_failed_last_run', 'never_backed_up', 'last_run_at']]);
    }

    public function test_disable_sets_status_100(): void
    {
        $device = Device::factory()->create();

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v2/devices/' . $device->id . '/disable')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('devices', ['id' => $device->id, 'status' => 100]);
    }

    public function test_enable_returns_success(): void
    {
        $device = Device::factory()->create(['status' => 100]);

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v2/devices/' . $device->id . '/enable')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_store_creates_device(): void
    {
        $payload = $this->validDevicePayload();

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v2/devices', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('devices', ['device_name' => $payload['device_name']]);

        Queue::assertPushed(DownloadConfigNowJob::class);
    }

    public function test_update_edits_device(): void
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory(1)->create();
        $commands = Command::factory(5)->create();
        $template = Template::factory(1)->create();
        $tags = Tag::factory(3)->create();

        foreach ($commands as $command) {
            DB::table('category_command')->insert([
                'command_id' => $command->id,
                'category_id' => $category->id,
            ]);
        }

        $device = Device::factory()->create(['device_category_id' => $category->id]);

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v2/devices/' . $device->id, [
                'device_name' => 'updated_device_v2',
                'device_ip' => '13.13.13.13',
                'device_username' => 'stacky',
                'device_password' => $device->device_password,
                'device_model' => $device->device_model,
                'device_vendor' => $vendor[0]['id'],
                'device_category_id' => $category->id,
                'device_tags' => $tags->pluck('id')->toArray(),
                'device_template' => $template[0]['id'],
                'device_main_prompt' => $device->device_main_prompt,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_name' => 'updated_device_v2',
        ]);
    }

    public function test_destroy_deletes_device(): void
    {
        $device = Device::factory()->create();

        $this->deleteJsonTolerant('/api/v2/devices/' . $device->id, $this->authHeader())
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    }
}
