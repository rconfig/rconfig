<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\User;
use Tests\TestCase;

class DeviceCredentialsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_device_credential_requires_a_name()
    {
        $response = $this->json('post', '/api/device-credentials', ['cred_name' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('cred_name', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_credential()
    {
        $credential = DeviceCredentials::factory()->create();
        $response = $this->get('/api/device-credentials/' . $credential->id);

        $response->assertJson(['cred_name' => $credential->cred_name]);
    }

    public function test_show_single_credential_with_device()
    {
        $credential = DeviceCredentials::factory()->create();
        $device = Device::factory()->create(['device_cred_id' => $credential->id]);

        $response = $this->get('/api/device-credentials/' . $credential->id);
        $response->assertStatus(200);

        $response->assertJson(['cred_name' => $credential->cred_name]);
        $this->assertEquals($device->device_name, $response->json()['device'][0]['device_name']);
    }

    public function test_get_all_device_credentials()
    {
        $category = DeviceCredentials::factory(100)->create();
        $response = $this->get('/api/device-credentials?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_device_credentials_with_generic_filter()
    {
        $cred = DeviceCredentials::factory()->create(['cred_name' => 'switch']);

        $response = $this->get('/api/device-credentials?page=1&perPage=100&filter[cred_name]=' . $cred->cred_name);
        $response->assertJsonFragment(['cred_name' =>  $cred->cred_name]);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    public function test_create_credential()
    {
        $cred = DeviceCredentials::factory()->make();
        $response = $this->post('/api/device-credentials', $cred->toArray());
        $response->assertStatus(200);

        $this->assertDatabaseHas('device_credentials', [
            'cred_name' => $cred->cred_name,
        ]);
    }


    public function test_edit_credential()
    {
        $credential = DeviceCredentials::factory()->create();

        $response = $this->json('patch', '/api/device-credentials/' . $credential->id, [
            'cred_name' => 'anewcredentialname',
            'cred_username' =>  'a new credentialDescription name',
            'cred_password' => 'a new credentialDescription name',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('device_credentials', [
            'id' => $credential->id,
            'cred_name' => 'anewcredentialname',
            'cred_username' => 'a new credentialDescription name',
        ]);
    }

    public function test_delete_category()
    {
        $credential = DeviceCredentials::factory()->create();

        $this->delete('/api/device-credentials/' . $credential->id);

        $this->assertDatabaseMissing('device_credentials', ['id' => $credential->id]);
    }

    public function test_cannot_delete_category_with_existing_device_relationships()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete credential set with related devices.');

        $credential = DeviceCredentials::factory()->create();
        $device = Device::factory()->create(['device_cred_id' => $credential->id]);

        $this->assertDatabaseHas('device_credentials', ['id' => $credential->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $credential->id]);

        $response = $this->delete('/api/device-credentials/' . $credential->id);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete credential set with related devices.']);

        // check again if the credential and device still exist
        $this->assertDatabaseHas('device_credentials', ['id' => $credential->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $credential->id]);

        // delete the command and the credential and the relationship
        $credential->delete();
        $device->delete();
    }


    public function test_deleteMany_returns_error_if_any_category_has_device_relationship()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete credential set with related devices.');

        $credential = DeviceCredentials::factory()->create();
        $credential2 = DeviceCredentials::factory()->create();
        $device = Device::factory()->create(['device_cred_id' => $credential->id]);

        $this->assertDatabaseHas('device_credentials', ['id' => $credential->id]);
        $this->assertDatabaseHas('device_credentials', ['id' => $credential2->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $credential->id]);

        $response = $this->post('/api/device-credentials/delete-many', ['ids' => [$credential->id, $credential2->id]]);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete credential set with related devices.']);

        // check again if the device_credentials and device still exist
        $this->assertDatabaseHas('device_credentials', ['id' => $credential->id]);
        $this->assertDatabaseHas('device_credentials', ['id' => $credential2->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_cred_id' => $credential->id]);

        $credential->delete();
        $credential2->delete();
        $device->delete();
    }

    public function test_get_category_device_relationship_but_not_disabled_devices()
    {
        $credential = DeviceCredentials::factory()->create();
        $device = Device::factory()->create(['device_cred_id' => $credential->id, 'status' => 100]);

        $cred = DeviceCredentials::with('device')->where('id', $credential->id)->get();

        $this->assertCount(1, $cred);
        $this->assertCount(0, $cred[0]->device);
    }
}
