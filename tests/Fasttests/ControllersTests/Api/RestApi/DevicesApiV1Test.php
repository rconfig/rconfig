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
use Illuminate\Support\Facades\Config;
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
function devicesApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

/**
 * Build a valid device store payload, mirroring DevicesControllerTest.
 *
 * @return array<string, mixed>
 */
function devicesApiV1ValidDevicePayload(): array
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
    Device::factory(5)->create();

    $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/devices?perPage=100')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('index masks credentials when enabled', function () {
    Config::set('rConfig.mask_device_credentials', true);
    Device::factory(3)->create();

    $response = $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/devices?perPage=100')
        ->assertStatus(200);

    $row = $response->json('data')[0];
    $this->assertArrayNotHasKey('device_username', $row);
    $this->assertArrayNotHasKey('device_password', $row);
    $this->assertArrayNotHasKey('device_enable_password', $row);

    Config::set('rConfig.mask_device_credentials', false);
});

test('show returns device', function () {
    $device = Device::factory()->create();

    $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/devices/' . $device->id)
        ->assertStatus(200)
        ->assertJsonFragment(['device_name' => $device->device_name]);
});

test('store creates device and dispatches jobs', function () {
    $payload = devicesApiV1ValidDevicePayload();

    $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->postJson('/api/v1/devices', $payload)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('devices', ['device_name' => $payload['device_name']]);

    Queue::assertPushed(DownloadConfigNowJob::class);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->postJson('/api/v1/devices', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['device_name', 'device_vendor', 'device_model', 'device_tags', 'device_template', 'device_main_prompt']);
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

    $this->withHeaders(devicesApiV1AuthHeader($this->token))
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
});

test('destroy deletes device', function () {
    $device = Device::factory()->create();

    $this->withHeaders(devicesApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/devices/' . $device->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
});
