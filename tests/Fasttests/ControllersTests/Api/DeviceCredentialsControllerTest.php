<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\User;
use Tests\TestCase;

class DeviceCredentialsControllerTest extends TestCase
{
     /** @var \App\Models\User */
    protected $user;

    protected $user2;
    protected $devices;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_get_all_creds()
    {
        DeviceCredentials::factory(20)->create();
        $response = $this->get('/api/settings/credentials?page=1&perPage=20');
        $this->assertEquals(20, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_creds_with_file()
    {
        DeviceCredentials::factory(20)->create();
        DeviceCredentials::factory()->create(['cred_name' => 'cisco123123']);
        $response = $this->get('/api/settings/credentials?page=1&perPage=100&filter[cred_name]=cisco123123');
        $this->assertEquals(1, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_a_devicecreds_requires_values()
    {
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
    }

    public function test_show_single_cred()
    {
        $cred = DeviceCredentials::factory()->create();
        $response = $this->get('/api/settings/credentials/' . $cred->id);

        $response->assertJson(['cred_name' => $cred->cred_name]);
    }

    public function test_create_cred()
    {
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
    }

    public function test_edit_cred()
    {
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
    }

    public function test_edit_cred_with_blank_cred_enable_password()
    {
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

        // verify that the cred_enable_password is null
        $response = $this->json('get', '/api/settings/credentials/' . $cred->id);
        $response->assertJsonFragment(['cred_enable_password' => null]);
    }

    public function test_delete_cred()
    {
        $cred = DeviceCredentials::factory()->create();

        $this->delete('/api/settings/credentials/' . $cred->id);

        $this->assertDatabaseMissing('device_credentials', ['id' => $cred->id]);
    }

    public function test_cannot_delete_cred_with_existing_device_relationships()
    {

        $cred = DeviceCredentials::factory()->create();
        // attached command to a category
        $device = Device::factory()->create(['device_cred_id' => $cred->id]);

        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $cred->id]);

        $response = $this->delete('/api/settings/credentials/' . $cred->id);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete credential set with related devices.']);

        $device->delete();
        $cred->delete();
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
