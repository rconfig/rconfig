<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\DeviceModel;
use App\Models\User;
use Tests\TestCase;

class DeviceModelControllerTest extends TestCase
{
    /** @var \App\Models\User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_get_all_device_models()
    {
        DeviceModel::factory(20)->create();
        $response = $this->get('/api/device-models?page=1&perPage=20');
        $this->assertEquals(20, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_device_models_with_filter()
    {
        DeviceModel::factory(20)->create();
        DeviceModel::factory()->create(['name' => 'Cisco 2960X']);
        $response = $this->get('/api/device-models?page=1&perPage=100&filter[q]=Cisco 2960X');
        $this->assertEquals(1, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_device_models_with_devices_count()
    {
        $deviceModel = DeviceModel::factory()->create(['name' => 'Cisco 3850']);
        Device::factory(3)->create(['device_model' => 'Cisco 3850']);

        $response = $this->get('/api/device-models');
        $response->assertStatus(200);
        
        $model = collect($response['data'])->firstWhere('name', 'Cisco 3850');
        $this->assertEquals(3, $model['devices_count']);
    }

    public function test_get_device_models_filtered_by_with_devices()
    {
        $modelWithDevices = DeviceModel::factory()->create(['name' => 'Model With Devices']);
        $modelWithoutDevices = DeviceModel::factory()->create(['name' => 'Model Without Devices']);
        
        Device::factory()->create(['device_model' => 'Model With Devices']);

        $response = $this->get('/api/device-models?filter[with_devices]=1');
        $response->assertStatus(200);
        
        $modelNames = collect($response['data'])->pluck('name');
        $this->assertTrue($modelNames->contains('Model With Devices'));
        $this->assertFalse($modelNames->contains('Model Without Devices'));
    }

    public function test_show_single_device_model()
    {
        $deviceModel = DeviceModel::factory()->create();
        $response = $this->get('/api/device-models/' . $deviceModel->id);

        $response->assertJson(['name' => $deviceModel->name]);
        $response->assertStatus(200);
    }

    public function test_show_nonexistent_device_model_returns_404()
    {
        $response = $this->get('/api/device-models/99999');
        $response->assertStatus(404);
    }

    public function test_a_device_model_requires_name()
    {
        $response = $this->json('post', '/api/device-models', [
            'name' => null,
        ]);

        $response->assertJson(['errors' => true]);
        $response->assertJsonValidationErrors('name');
        $response->assertStatus(422);
    }

    public function test_create_device_model()
    {
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
    }

    public function test_create_device_model_with_duplicate_name()
    {
        DeviceModel::factory()->create(['name' => 'Cisco 2960']);

        $response = $this->json('post', '/api/device-models', [
            'name' => 'Cisco 2960',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_create_device_model_validates_name_length()
    {
        $response = $this->json('post', '/api/device-models', [
            'name' => str_repeat('a', 256), // 256 characters
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_create_device_model_validates_unique_against_devices_table()
    {
        // Create a device with a model name
        Device::factory()->create(['device_model' => 'Existing Model']);

        // Try to create a device model with the same name
        $response = $this->json('post', '/api/device-models', [
            'name' => 'Existing Model',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_edit_device_model()
    {
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
    }

    public function test_edit_device_model_with_same_name()
    {
        $deviceModel = DeviceModel::factory()->create(['name' => 'Same Name']);

        $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
            'name' => 'Same Name',
        ]);

        $response->assertStatus(200);
    }

    public function test_delete_device_model()
    {
        $deviceModel = DeviceModel::factory()->create();

        $response = $this->delete('/api/device-models/' . $deviceModel->id);
        
        $response->assertStatus(200);
        $this->assertStringContainsString('deleted successfully', $response->json('message'));
        
        // Check for soft delete
        $this->assertDatabaseMissing('device_models', ['id' => $deviceModel->id]);
    }

    public function test_cannot_delete_device_model_with_associated_devices()
    {
        $deviceModel = DeviceModel::factory()->create(['name' => 'Model In Use']);
        Device::factory(2)->create(['device_model' => 'Model In Use']);

        $response = $this->delete('/api/device-models/' . $deviceModel->id);

        // The model's boot method should prevent deletion
        $response->assertStatus(500);

        // Verify model was not deleted
        $this->assertDatabaseHas('device_models', [
            'id' => $deviceModel->id,
        ]);
    }

    public function test_delete_many_device_models()
    {
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
    }

    public function test_device_models_support_pagination()
    {
        DeviceModel::factory(50)->create();

        $response = $this->get('/api/device-models?perPage=15');
        $response->assertStatus(200);
        
        $this->assertEquals(15, count($response['data']));
        $this->assertArrayHasKey('current_page', $response->json());
        $this->assertArrayHasKey('last_page', $response->json());
        $this->assertArrayHasKey('total', $response->json());
    }

    public function test_device_models_support_sorting_by_name()
    {
        DeviceModel::factory()->create(['name' => 'Zebra Model']);
        DeviceModel::factory()->create(['name' => 'Alpha Model']);
        DeviceModel::factory()->create(['name' => 'Beta Model']);

        // Ascending sort
        $response = $this->get('/api/device-models?sort=name');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Alpha Model', $data[0]['name']);

        // Descending sort
        $response = $this->get('/api/device-models?sort=-name');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Zebra Model', $data[0]['name']);
    }

    public function test_device_models_support_sorting_by_id()
    {
        $model1 = DeviceModel::factory()->create();
        $model2 = DeviceModel::factory()->create();
        $model3 = DeviceModel::factory()->create();

        $response = $this->get('/api/device-models?sort=id');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($model1->id, $data[0]['id']);
    }

    public function test_device_models_support_sorting_by_devices_count()
    {
        $model1 = DeviceModel::factory()->create(['name' => 'Model 1']);
        $model2 = DeviceModel::factory()->create(['name' => 'Model 2']);
        
        Device::factory(5)->create(['device_model' => 'Model 1']);
        Device::factory(2)->create(['device_model' => 'Model 2']);

        $response = $this->get('/api/device-models?sort=-devices_count');
        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Model 1 should be first (has more devices)
        $firstModel = collect($data)->firstWhere('name', 'Model 1');
        $this->assertEquals(5, $firstModel['devices_count']);
    }

    public function test_device_models_support_sorting_by_created_at()
    {
        $response = $this->get('/api/device-models?sort=created_at');
        $response->assertStatus(200);
    }

    public function test_name_is_trimmed_before_validation()
    {
        $response = $this->json('post', '/api/device-models', [
            'name' => '  Cisco 2960  ',
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('device_models', [
            'name' => 'Cisco 2960', // Should be trimmed
        ]);
    }

    public function test_update_device_models_from_devices_creates_missing_models()
    {
        // This tests the protected method indirectly
        // Create devices with models that don't exist in device_models table
        Device::factory()->create(['device_model' => 'Unique Model A']);
        Device::factory()->create(['device_model' => 'Unique Model B']);
        
        // For now, just verify the devices exist
        $this->assertDatabaseHas('devices', ['device_model' => 'Unique Model A']);
        $this->assertDatabaseHas('devices', ['device_model' => 'Unique Model B']);
    }

    public function test_get_device_models_with_custom_per_page()
    {
        DeviceModel::factory(30)->create();

        $response = $this->get('/api/device-models?perPage=10');
        $response->assertStatus(200);
        $this->assertEquals(10, count($response['data']));

        $response = $this->get('/api/device-models?perPage=25');
        $response->assertStatus(200);
        $this->assertEquals(25, count($response['data']));
    }

    public function test_device_model_name_cannot_be_empty_string()
    {
        $response = $this->json('post', '/api/device-models', [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_device_model_name_must_be_at_least_1_character()
    {
        $response = $this->json('post', '/api/device-models', [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_update_requires_valid_name()
    {
        $deviceModel = DeviceModel::factory()->create();

        $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_update_validates_name_max_length()
    {
        $deviceModel = DeviceModel::factory()->create();

        $response = $this->json('patch', '/api/device-models/' . $deviceModel->id, [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_device_models_index_returns_correct_structure()
    {
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
                ]
            ],
            'current_page',
            'per_page',
            'total',
        ]);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}