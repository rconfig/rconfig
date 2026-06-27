<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Jobs\DownloadConfigNowJob;
use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Queue;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class DevicesApiV1Test extends TestCase
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
     * Build a valid device store payload, mirroring DevicesControllerTest.
     *
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
        Device::factory(5)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/devices?perPage=100')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_index_masks_credentials_when_enabled(): void
    {
        Config::set('rConfig.mask_device_credentials', true);
        Device::factory(3)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/devices?perPage=100')
            ->assertStatus(200);

        $row = $response->json('data')[0];
        $this->assertArrayNotHasKey('device_username', $row);
        $this->assertArrayNotHasKey('device_password', $row);
        $this->assertArrayNotHasKey('device_enable_password', $row);

        Config::set('rConfig.mask_device_credentials', false);
    }

    public function test_show_returns_device(): void
    {
        $device = Device::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/devices/' . $device->id)
            ->assertStatus(200)
            ->assertJsonFragment(['device_name' => $device->device_name]);
    }

    public function test_store_creates_device_and_dispatches_jobs(): void
    {
        $payload = $this->validDevicePayload();

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/devices', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('devices', ['device_name' => $payload['device_name']]);

        Queue::assertPushed(DownloadConfigNowJob::class);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/devices', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['device_name', 'device_vendor', 'device_model', 'device_tags', 'device_template', 'device_main_prompt']);
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
            ->patchJson('/api/v1/devices/' . $device->id, [
                'device_name' => 'updated_device_name',
                'device_ip' => '12.12.12.12',
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
            'device_name' => 'updated_device_name',
            'device_ip' => '12.12.12.12',
        ]);
    }

    public function test_destroy_deletes_device(): void
    {
        $device = Device::factory()->create();

        $this->deleteJsonTolerant('/api/v1/devices/' . $device->id, $this->authHeader())
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    }
}
