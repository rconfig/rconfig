<?php

use App\Jobs\DownloadConfigNowJob;
use App\Models\Category;
use App\Models\Command;
use App\Models\Config as DeviceConfig;
use App\Models\Device;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use App\Observers\DeviceObserver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all devices', function () {
    $devices = Device::factory(17)->create(['device_model' => 'DevicesControllerTestCSR1000v']);
    $response = $this->get('/api/devices?page=1&perPage=1000');
    $response->assertStatus(200);
});

test('get all devices but not creds when password mask is enabled', function () {
    Device::factory(2)->create();

    Config::set('rConfig.mask_device_credentials', true);
    expect(Config::get('rConfig.mask_device_credentials'))->toBeTrue();

    $response = $this->get('/api/devices?page=1&perPage=100');
    $response->assertStatus(200);

    // check a device did not send back username and passwords
    $this->assertArrayNotHasKey('device_username', $response->json()['data'][1]);
    $this->assertArrayNotHasKey('device_password', $response->json()['data'][1]);
    $this->assertArrayNotHasKey('device_enable_password', $response->json()['data'][1]);

    Config::set('rConfig.mask_device_credentials', false);
    expect(Config::get('rConfig.mask_device_credentials'))->toBeFalse();
});

test('devices have relationships', function () {
    Device::factory(100)->create();
    $response = $this->get('/api/devices?page=1&perPage=100');
    $response->assertJsonFragment(['category' => []]);
    $response->assertJsonFragment(['tag' => []]);
    $response->assertJsonFragment(['vendor' => []]);
    $response->assertJsonFragment(['template' => []]);
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('get all devices with generic filter', function () {
    $sharedIp = '203.0.113.5';
    Device::factory(4)->create(['device_ip' => $sharedIp]);

    $response = $this->get('/api/devices?page=1&perPage=100&filter[q]=' . $sharedIp);
    $response->assertJsonFragment(['device_ip' => $sharedIp]);
    $response->assertJsonFragment(['total' => 4]);
    $response->assertStatus(200);
});

test('get one device', function () {
    $device = Device::factory()->create();
    $response = $this->get('/api/devices/' . $device->id);

    $response->assertStatus(200);
    $response->assertJsonFragment(
        [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'device_password' => $device->device_password,
        ]
    );
    $response->assertJsonStructure(
        [
            'vendor',
            'category',
            'tag',
            'template',
        ]
    );
});

test('get list of a devices commands', function () {
    $category = Category::factory()->create();
    $device = Device::factory()->create((['device_category_id' => $category->id]));
    $commands = Command::factory(5)->create();

    foreach ($commands as $command) {
        DB::table('category_command')->insert(
            [
                'command_id' => $command->id,
                'category_id' => $device->device_category_id,
            ]
        );
    }

    DB::table('category_device')->insert(
        [
            'device_id' => $device->id,
            'category_id' => $category->id,
        ]
    );

    $this->assertDatabaseHas('category_command', [
        'command_id' => $commands[0]->id,
        'category_id' => $device->device_category_id,
    ]);
    $this->assertDatabaseHas('category_device', [
        'device_id' => $device->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/devices/' . $device->id);
    $response->assertJsonFragment([
        'categoryName' => $category->categoryName,
    ]);
    expect($response->json()['category'][0]['command'])->toHaveCount(5);
    $response->assertStatus(200);
});

test('a device requires fields', function () {
    $response = $this->json('post', '/api/devices');
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['device_name', 'device_vendor', 'device_model', 'device_tags', 'device_template', 'device_main_prompt']);
});

test('create device', function () {
    Queue::fake();

    $category = Category::factory()->create();
    $vendor = Vendor::factory(1)->create();
    $commands = Command::factory(5)->create();
    $template = Template::factory(1)->create();
    $tags = Tag::factory(3)->create();

    foreach ($commands as $command) {
        DB::table('category_command')->insert(
            [
                'command_id' => $command->id,
                'category_id' => $category->id,
            ]
        );
    }

    $device = Device::factory()->make([
        'device_category_id' => $category->id,
        'device_vendor' => $vendor->toArray(),
        'device_tags' => $tags,
        'device_template' => $template->toArray(),
    ]);

    $this->assertDatabaseHas('category_command', [
        'command_id' => $commands[0]->id,
        'category_id' => $device->device_category_id,
    ]);

    $this->assertDatabaseMissing('devices', [
        'id' => $device->id,
    ]);

    $response = $this->json('post', '/api/devices', $device->toArray());
    $response->assertStatus(200);

    $result = json_decode($response->getContent());

    $this->assertDatabaseHas('devices', [
        'id' => $result->data->id,
    ]);

    $this->assertDatabaseHas('devices', [
        'device_username' => $device->device_username,
        'device_name' => $device->device_name,
    ]);

    $this->assertDatabaseHas('device_template', [
        'device_id' => $result->data->id,
        'template_id' => $device->device_template[0]['id'],
    ]);

    $this->assertDatabaseHas('device_tag', [
        'device_id' => $result->data->id,
        'tag_id' => $device->device_tags[0]->id,
    ]);

    $this->assertDatabaseHas('device_vendor', [
        'device_id' => $result->data->id,
        'vendor_id' => $device->device_vendor[0]['id'],
    ]);

    Queue::assertPushed(DownloadConfigNowJob::class);
});

test('edit device', function () {
    $category = Category::factory()->create();
    $vendor = Vendor::factory(1)->create();
    $commands = Command::factory(5)->create();
    $template = Template::factory(1)->create();
    $tags = Tag::factory(3)->create();

    foreach ($commands as $command) {
        DB::table('category_command')->insert(
            [
                'command_id' => $command->id,
                'category_id' => $category->id,
            ]
        );
    }

    $device = Device::factory()->create([
        'device_category_id' => $category->id,
    ]);

    $this->assertDatabaseHas('devices', [
        'id' => $device->id,
        'device_name' => $device->device_name,
    ]);

    $response = $this->patch('/api/devices/' . $device->id, [
        'device_name' => 'a_new_device_name',
        'device_ip' => '12.12.12.12',
        'device_username' => 'stacky',
        'device_password' => $device->device_password,
        'device_model' => $device->device_model,
        'device_vendor' => $vendor[0]['id'],
        'device_category_id' => $category->id,
        'device_tags' => $tags->pluck('id')->toArray(),
        'device_template' => $template[0]['id'],
        'device_main_prompt' => $device->device_main_prompt,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('devices', [
        'id' => $device->id,
        'device_name' => 'a_new_device_name',
        'device_ip' => '12.12.12.12',
        'device_username' => 'stacky',
    ]);
});

test('delete device removes configs and config changes', function () {
    // Observer registration is skipped in tests by default; register it here to exercise the cleanup logic.
    Device::observe(DeviceObserver::class);

    $device = Device::factory()->create();

    $configOne = DeviceConfig::factory()->create([
        'device_id' => $device->id,
        'device_name' => $device->device_name,
        'config_location' => 'tests/storage/configs/' . $device->id . '_one.txt', // avoid deleting real files
    ]);
    $configTwo = DeviceConfig::factory()->create([
        'device_id' => $device->id,
        'device_name' => $device->device_name,
        'config_location' => 'tests/storage/configs/' . $device->id . '_two.txt',
    ]);

    $this->delete('/api/devices/' . $device->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    $this->assertDatabaseMissing('configs', ['id' => $configOne->id]);
    $this->assertDatabaseMissing('configs', ['id' => $configTwo->id]);
});

test('delete device', function () {
    $device = Device::factory()->create();
    $this->assertDatabaseHas('devices', ['id' => $device->id]);

    $this->delete('/api/devices/' . $device->id);

    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
});

test('add serialised encrypted password and decrypt correctly if pw is serialised v5 migration bug', function () {
    $nativelyEncryptedDevice = Device::factory()->create([
        'device_password' => 'cisco',
        'device_enable_password' => 'cisco',
    ]);

    Device::where('id', 1111111)->delete();
    DB::table('devices')->insert([
        'id' => 1111111,
        'device_name' => 'test_device',
        'device_ip' => '1.1.1.1',
        'device_password' => Crypt::encrypt('v5_encrypted_password'),
        'device_enable_password' => Crypt::encrypt('v5_encrypted_password'),
        'device_template' => 1,
        'device_model' => 'DevicesControllerTestCSR1000v',
    ]);
    $device = Device::where('id', 1111111)->first();

    // dd(Device::all());
    $v5EncryptedPassword = Device::select('device_password')->where('id', 1111111)->first();

    // dd($v5EncryptedPassword->device_password);
    $v6EncryptedPassword = Device::select('device_password')->where('id', $nativelyEncryptedDevice->id)->first();
    expect(is_serialized($v5EncryptedPassword->device_password))->toBeFalse();
    expect(is_serialized($v6EncryptedPassword->device_password))->toBeFalse();

    $response = $this->get('/api/devices/' . $nativelyEncryptedDevice->id);
    $response->assertStatus(200);
    $response->assertJson([
        'id' => $nativelyEncryptedDevice->id,
        'device_password' => 'cisco',
        'device_enable_password' => 'cisco',
    ]);

    $response = $this->get('/api/devices/' . 1111111);
    $response->assertStatus(200);
    $response->assertJson([
        'id' => 1111111,
        'device_password' => 'v5_encrypted_password',
        'device_enable_password' => 'v5_encrypted_password',
    ]);
    $response = $this->json('delete', '/api/devices/' . 1111111);
    $this->assertDatabaseMissing('devices', ['id' => 1111111]);
});

function is_serialized($string)
{
    try {
        unserialize($string);
    } catch (Exception $e) {
        return false;
    }

    return true;
}
