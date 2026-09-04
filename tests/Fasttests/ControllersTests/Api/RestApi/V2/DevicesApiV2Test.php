<?php

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

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
    Queue::fake();
});

/**
 * @return array<string, string>
 */
function devicesApiV2AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

/**
 * @return array<string, mixed>
 */
function devicesApiV2ValidDevicePayload(): array
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

test('index returns paginated devices', function () {
    Device::factory(3)->create();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/devices?perPage=100')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns device', function () {
    $device = Device::factory()->create();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/devices/' . $device->id)
        ->assertStatus(200)
        ->assertJsonFragment(['device_name' => $device->device_name]);
});

test('summary returns expected keys', function () {
    Device::factory(4)->create();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/devices/summary')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['total_devices', 'backup_success_last_run', 'backup_failed_last_run', 'never_backed_up', 'last_run_at']]);
});

test('disable sets status 100', function () {
    $device = Device::factory()->create();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->postJson('/api/v2/devices/' . $device->id . '/disable')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('devices', ['id' => $device->id, 'status' => 100]);
});

test('enable returns success', function () {
    $device = Device::factory()->create(['status' => 100]);

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->postJson('/api/v2/devices/' . $device->id . '/enable')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('store creates device', function () {
    $payload = devicesApiV2ValidDevicePayload();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->postJson('/api/v2/devices', $payload)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('devices', ['device_name' => $payload['device_name']]);

    Queue::assertPushed(DownloadConfigNowJob::class);
});

test('update edits device', function () {
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

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
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
});

test('destroy deletes device', function () {
    $device = Device::factory()->create();

    $this->withHeaders(devicesApiV2AuthHeader($this->token))
        ->deleteJson('/api/v2/devices/' . $device->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
});
