<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;
use Tests\TestCase;

class VendorsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

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
        $vendor = \App\Models\Vendor::factory()->create();
        $response = $this->get('/api/vendors/' . $vendor->id);

        $response->assertJson(['vendorName' => $vendor->vendorName]);
    }

    public function test_get_all_vendors()
    {
        $vendor = \App\Models\Vendor::factory(100)->create();
        $response = $this->get('/api/vendors?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_vendor()
    {
        $vendor = \App\Models\Vendor::factory()->create();
        $this->post('/api/vendors', $vendor->toArray());

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'vendorName' => $vendor->vendorName,
        ]);
    }

    public function test_edit_vendor()
    {
        $vendor = \App\Models\Vendor::factory()->create();

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
        $vendor = \App\Models\Vendor::factory()->create();

        $this->delete('/api/vendors/' . $vendor->id);

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
