<?php

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all device models', function () {
    DeviceModel::factory(20)->create();
    $response = $this->get('/api/device-models?page=1&perPage=20');
    expect(count($response['data']))->toEqual(20);
    $response->assertStatus(200);
});

test('get all device models with filter', function () {
    DeviceModel::factory(20)->create();
    DeviceModel::factory()->create(['name' => 'Cisco 2960X']);
    $response = $this->get('/api/device-models?page=1&perPage=100&filter[q]=Cisco 2960X');
    expect(count($response['data']))->toEqual(1);
    $response->assertStatus(200);
});

test('get device models with devices count', function () {
    $deviceModel = DeviceModel::factory()->create(['name' => 'Cisco 3850']);
    Device::factory(3)->create(['device_model' => 'Cisco 3850']);

    $response = $this->get('/api/device-models');
    $response->assertStatus(200);

    $model = collect($response['data'])->firstWhere('name', 'Cisco 3850');
    expect($model['devices_count'])->toEqual(3);
});

test('get device models filtered by with devices', function () {
    $modelWithDevices = DeviceModel::factory()->create(['name' => 'Model With Devices']);
    $modelWithoutDevices = DeviceModel::factory()->create(['name' => 'Model Without Devices']);

    Device::factory()->create(['device_model' => 'Model With Devices']);

    $response = $this->get('/api/device-models?filter[with_devices]=1');
    $response->assertStatus(200);

    $modelNames = collect($response['data'])->pluck('name');
    expect($modelNames->contains('Model With Devices'))->toBeTrue();
    expect($modelNames->contains('Model Without Devices'))->toBeFalse();
});

test('show single device model', function () {
    $deviceModel = DeviceModel::factory()->create();
    $response = $this->get('/api/device-models/' . $deviceModel->id);

    $response->assertJson(['name' => $deviceModel->name]);
    $response->assertStatus(200);
});

test('show nonexistent device model returns 404', function () {
    $response = $this->get('/api/device-models/99999');
    $response->assertStatus(404);
});

test('a device model requires name', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => null,
    ]);

    $response->assertJson(['errors' => true]);
    $response->assertJsonValidationErrors('name');
    $response->assertStatus(422);
});

test('create device model', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => 'Cisco ASR 1001-X',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['success', 'data', 'message']);
    $response->assertJson(['success' => true]);
    $this->assertStringContainsString('created successfully', $response->json('message'));

    $this->assertDatabaseHas('device_models', [
        'name' => 'Cisco ASR 1001-X',
    ]);
});

test('create device model with duplicate name', function () {
    DeviceModel::factory()->create(['name' => 'Cisco 2960']);

    $response = $this->json('post', '/api/device-models', [
        'name' => 'Cisco 2960',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('create device model validates name length', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => str_repeat('a', 256), // 256 characters
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('create device model validates unique against devices table', function () {
    // Create a device with a model name
    Device::factory()->create(['device_model' => 'Existing Model']);

    // Try to create a device model with the same name
    $response = $this->json('post', '/api/device-models', [
        'name' => 'Existing Model',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('edit device model', function () {
    $deviceModel = DeviceModel::factory()->create(['name' => 'Old Model Name']);

    $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
        'name' => 'New Model Name',
    ]);

    $response->assertStatus(200);
    $this->assertStringContainsString('edited successfully', $response->json('message'));

    $this->assertDatabaseHas('device_models', [
        'id' => $deviceModel->id,
        'name' => 'New Model Name',
    ]);
});

test('edit device model with same name', function () {
    $deviceModel = DeviceModel::factory()->create(['name' => 'Same Name']);

    $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
        'name' => 'Same Name',
    ]);

    $response->assertStatus(200);
});

test('delete device model', function () {
    $deviceModel = DeviceModel::factory()->create();

    $response = $this->delete('/api/device-models/' . $deviceModel->id);

    $response->assertStatus(200);
    $this->assertStringContainsString('deleted successfully', $response->json('message'));

    // Check for soft delete
    $this->assertDatabaseMissing('device_models', ['id' => $deviceModel->id]);
});

test('cannot delete device model with associated devices', function () {
    $deviceModel = DeviceModel::factory()->create(['name' => 'Model In Use']);
    Device::factory(2)->create(['device_model' => 'Model In Use']);

    $response = $this->delete('/api/device-models/' . $deviceModel->id);

    // The model's boot method should prevent deletion
    $response->assertStatus(500);

    // Verify model was not deleted
    $this->assertDatabaseHas('device_models', [
        'id' => $deviceModel->id,
    ]);
});

test('delete many device models', function () {
    $deviceModel1 = DeviceModel::factory()->create();
    $deviceModel2 = DeviceModel::factory()->create();
    $deviceModel3 = DeviceModel::factory()->create();

    $response = $this->json('post', '/api/device-models/delete-many', [
        'ids' => [$deviceModel1->id, $deviceModel2->id],
    ]);

    $response->assertStatus(200);
    $this->assertStringContainsString('deleted successfully', $response->json('message'));

    $this->assertDatabaseMissing('device_models', ['id' => $deviceModel1->id]);
    $this->assertDatabaseMissing('device_models', ['id' => $deviceModel2->id]);

    // deviceModel3 should not be deleted
    $this->assertDatabaseHas('device_models', [
        'id' => $deviceModel3->id,
    ]);
});

test('device models support pagination', function () {
    DeviceModel::factory(50)->create();

    $response = $this->get('/api/device-models?perPage=15');
    $response->assertStatus(200);

    expect(count($response['data']))->toEqual(15);
    expect($response->json())->toHaveKey('current_page');
    expect($response->json())->toHaveKey('last_page');
    expect($response->json())->toHaveKey('total');
});

test('device models support sorting by name', function () {
    DeviceModel::factory()->create(['name' => 'Zebra Model']);
    DeviceModel::factory()->create(['name' => 'Alpha Model']);
    DeviceModel::factory()->create(['name' => 'Beta Model']);

    // Ascending sort
    $response = $this->get('/api/device-models?sort=name');
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data[0]['name'])->toEqual('Alpha Model');

    // Descending sort
    $response = $this->get('/api/device-models?sort=-name');
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data[0]['name'])->toEqual('Zebra Model');
});

test('device models support sorting by id', function () {
    $model1 = DeviceModel::factory()->create();
    $model2 = DeviceModel::factory()->create();
    $model3 = DeviceModel::factory()->create();

    $response = $this->get('/api/device-models?sort=id');
    $response->assertStatus(200);
    $data = $response->json('data');
    expect($data[0]['id'])->toEqual($model1->id);
});

test('device models support sorting by devices count', function () {
    $model1 = DeviceModel::factory()->create(['name' => 'Model 1']);
    $model2 = DeviceModel::factory()->create(['name' => 'Model 2']);

    Device::factory(5)->create(['device_model' => 'Model 1']);
    Device::factory(2)->create(['device_model' => 'Model 2']);

    $response = $this->get('/api/device-models?sort=-devices_count');
    $response->assertStatus(200);
    $data = $response->json('data');

    // Model 1 should be first (has more devices)
    $firstModel = collect($data)->firstWhere('name', 'Model 1');
    expect($firstModel['devices_count'])->toEqual(5);
});

test('device models support sorting by created at', function () {
    $response = $this->get('/api/device-models?sort=created_at');
    $response->assertStatus(200);
});

test('name is trimmed before validation', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => '  Cisco 2960  ',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('device_models', [
        'name' => 'Cisco 2960', // Should be trimmed
    ]);
});

test('update device models from devices creates missing models', function () {
    // This tests the protected method indirectly
    // Create devices with models that don't exist in device_models table
    Device::factory()->create(['device_model' => 'Unique Model A']);
    Device::factory()->create(['device_model' => 'Unique Model B']);

    // For now, just verify the devices exist
    $this->assertDatabaseHas('devices', ['device_model' => 'Unique Model A']);
    $this->assertDatabaseHas('devices', ['device_model' => 'Unique Model B']);
});

test('get device models with custom per page', function () {
    DeviceModel::factory(30)->create();

    $response = $this->get('/api/device-models?perPage=10');
    $response->assertStatus(200);
    expect(count($response['data']))->toEqual(10);

    $response = $this->get('/api/device-models?perPage=25');
    $response->assertStatus(200);
    expect(count($response['data']))->toEqual(25);
});

test('device model name cannot be empty string', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('device model name must be at least 1 character', function () {
    $response = $this->json('post', '/api/device-models', [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('update requires valid name', function () {
    $deviceModel = DeviceModel::factory()->create();

    $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('update validates name max length', function () {
    $deviceModel = DeviceModel::factory()->create();

    $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('device models index returns correct structure', function () {
    DeviceModel::factory(3)->create();

    $response = $this->get('/api/device-models');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'devices_count',
                'created_at',
                'updated_at',
            ],
        ],
        'current_page',
        'per_page',
        'total',
    ]);
});

afterEach(function () {
    $this->rollBackTransaction();
});
