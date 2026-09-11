<?php

use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all creds', function () {
    DeviceCredentials::factory(20)->create();
    $response = $this->get('/api/settings/credentials?page=1&perPage=20');
    expect(count($response['data']))->toEqual(20);
    $response->assertStatus(200);
});

test('get all creds with file', function () {
    DeviceCredentials::factory(20)->create();
    DeviceCredentials::factory()->create(['cred_name' => 'cisco123123']);
    $response = $this->get('/api/settings/credentials?page=1&perPage=100&filter[cred_name]=cisco123123');
    expect(count($response['data']))->toEqual(1);
    $response->assertStatus(200);
});

test('a devicecreds requires values', function () {
    $response = $this->json('post', '/api/settings/credentials', [
        'cred_name' => null,
        'vault_enabled' => 0,
    ]);

    $response->assertJson(['errors' => true]);
    $response->assertJsonValidationErrors('cred_name');
    $response->assertJsonValidationErrors('cred_username');

    // $response->assertJsonValidationErrors('cred_password');
    $response->assertJsonMissingValidationErrors(['cred_description']);

    // $response->assertJsonMissingValidationErrors(['cred_enable_password']);
    $response->assertJsonMissingValidationErrors(['cred_is_default']);

    $response->assertStatus(422);
});

test('show single cred', function () {
    $cred = DeviceCredentials::factory()->create();
    $response = $this->get('/api/settings/credentials/' . $cred->id);

    $response->assertJson(['cred_name' => $cred->cred_name]);
});

test('create cred', function () {
    $cred = DeviceCredentials::factory()->make();
    $response = $this->json('post', '/api/settings/credentials', $cred->toArray());

    $response->assertStatus(200);
    $response->assertJsonStructure(['success', 'data', 'message']);
    $response->assertJson(['success' => true]);

    // Just check that some success message is returned
    $this->assertStringContainsString('created successfully', $response->json('message'));

    $this->assertDatabaseHas('device_credentials', [
        'cred_name' => $cred->cred_name,
    ]);
});

test('edit cred', function () {
    $cred = DeviceCredentials::factory()->create();

    $response = $this->json('patch', '/api/settings/credentials/' . $cred->id, [
        'cred_name' => 'a new cred name',
        'cred_description' => 'a new credDescription name',
        'cred_username' => 'a new credDescription name',
        'cred_password' => 'a new credDescription name',
        'cred_enable_password' => 'a new credDescription name',
        'cred_is_default' => 0,
        'vault_enabled' => 0,
    ]);

    $this->assertDatabaseHas('device_credentials', [
        'id' => $cred->id,
        'cred_name' => 'a new cred name',
        'cred_description' => 'a new credDescription name',
    ]);
});

test('edit cred with blank cred enable password', function () {
    $cred = DeviceCredentials::factory()->create(['cred_enable_password' => '']);

    $response = $this->json('patch', '/api/settings/credentials/' . $cred->id, [
        'cred_name' => 'a new cred name',
        'cred_description' => 'a new credDescription name',
        'cred_username' => 'a new credDescription name',
        'cred_password' => 'a new credPassword',
        'cred_enable_password' => null,
        'cred_is_default' => 0,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('device_credentials', [
        'id' => $cred->id,
        'cred_name' => 'a new cred name',
        'cred_description' => 'a new credDescription name',
    ]);

    // verify that blank/disabled enable password stays blank-like after update
    $response = $this->json('get', '/api/settings/credentials/' . $cred->id);
    $enablePassword = $response->json('cred_enable_password');
    expect([null, '', 0, '0'])->toContain($enablePassword);
});

test('delete cred', function () {
    $cred = DeviceCredentials::factory()->create();

    $this->delete('/api/settings/credentials/' . $cred->id);

    $this->assertDatabaseMissing('device_credentials', ['id' => $cred->id]);
});

test('cannot delete cred with existing device relationships', function () {
    $cred = DeviceCredentials::factory()->create();

    // attached command to a category
    $device = Device::factory()->create(['device_cred_id' => $cred->id]);

    $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $cred->id]);

    $response = $this->delete('/api/settings/credentials/' . $cred->id);

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Cannot delete credential set with related devices.']);

    $device->delete();
    $cred->delete();
});

afterEach(function () {
    $this->rollBackTransaction();
});
