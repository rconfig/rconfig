<?php

use App\Models\User;
use App\Models\Vendor;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('a vendor requires a name', function () {
    $response = $this->json('post', '/api/vendors', ['vendorName' => null]);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('vendorName');
    $response->assertStatus(422);
});

test('show single vendor', function () {
    $vendor = Vendor::factory()->create();
    $response = $this->get('/api/vendors/' . $vendor->id);

    $response->assertJson(['vendorName' => $vendor->vendorName]);
});

test('get all vendors', function () {
    $vendor = Vendor::factory(100)->create();
    $response = $this->get('/api/vendors?page=1&perPage=100');
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('create vendor', function () {
    $vendor = Vendor::factory()->create();
    $this->post('/api/vendors', $vendor->toArray());

    $this->assertDatabaseHas('vendors', [
        'id' => $vendor->id,
        'vendorName' => $vendor->vendorName,
    ]);
});

test('edit vendor', function () {
    $vendor = Vendor::factory()->create();

    $response = $this->patch('/api/vendors/' . $vendor->id, [
        'vendorName' => 'a-new-vendor-name',
    ]);

    $this->assertDatabaseHas('vendors', [
        'id' => $vendor->id,
        'vendorName' => 'a-new-vendor-name',
    ]);
});

test('delete vendor', function () {
    $vendor = Vendor::factory()->create();

    $this->delete('/api/vendors/' . $vendor->id);

    $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
});

afterEach(function () {
    $this->rollBackTransaction();
});
