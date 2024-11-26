<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

class VendorsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_vendor_requires_a_name()
    {
        $response = $this->json('post', '/api/vendors', ['vendorName' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('vendorName', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_vendor()
    {
        $vendor = Vendor::factory()->create();
        $response = $this->get('/api/vendors/' . $vendor->id);

        $response->assertJson(['vendorName' => $vendor->vendorName]);
    }

    public function test_get_all_categories_with_generic_filter()
    {

        $vendor = Vendor::factory()->create();

        $response = $this->get('/api/vendors?page=1&perPage=100&filter[vendorName]=' . $vendor->vendorName);
        $response->assertJsonFragment(['vendorName' => $vendor->vendorName]);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    public function test_get_all_vendors()
    {
        $vendor = Vendor::factory(100)->create();
        $response = $this->get('/api/vendors?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_vendor()
    {
        $vendor = Vendor::factory()->create();
        $this->post('/api/vendors', $vendor->toArray());

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendorName' => $vendor->vendorName,
        ]);
    }

    public function test_edit_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->patch('/api/vendors/' . $vendor->id, [
            'vendorName' => 'a-new-vendor-name',
        ]);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendorName' => 'a-new-vendor-name',
        ]);
    }

    public function test_delete_vendor()
    {
        $vendor = Vendor::factory()->create();

        $this->delete('/api/vendors/' . $vendor->id);

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_cannot_delete_vendor_with_existing_device_relationships()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete vendor with related devices.');

        $vendor = Vendor::factory()->create();
        $device = Device::factory()->create();
        // attached vendor to a device
        $device->vendor()->attach($vendor->id);

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        $response = $this->delete('/api/vendors/' . $vendor->id);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete vendor with related devices.']);

        // check again if the vendor and device still exist
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        // delete the command and the vendor and the relationship
        $vendor->delete();
        $device->vendor()->detach($vendor->id);
        $device->delete();
    }

    public function test_deleteMany_returns_error_if_any_vendor_has_device_relationship()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete vendor with related devices.');

        $vendor = Vendor::factory()->create();
        $vendor2 = Vendor::factory()->create();
        $device = Device::factory()->create();
        // attached vendor to a device
        $device->vendor()->attach($vendor->id);

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        $response = $this->post('/api/vendors/delete-many', ['ids' => [$vendor->id, $vendor2->id]]);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete vendor with related devices.']);

        // check again if the vendor and device still exist
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
        $this->assertDatabaseHas('vendors', ['id' => $vendor2->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        // delete the command and the vendor and the relationship
        $vendor->delete();
        $device->vendor()->detach($vendor->id);
        $device->delete();
    }

    public function test_get_vendor_device_relationship_but_not_disabled_devices()
    {
        $device = Device::factory()->create(['status' => 100]);
        $vendor = Vendor::factory()->create();
        $vendor->device()->attach($device->id);

        $cat = Vendor::with('device')->where('id', $vendor->id)->get();

        $this->assertCount(1, $cat);
        $this->assertCount(0, $cat[0]->device);
    }
}
