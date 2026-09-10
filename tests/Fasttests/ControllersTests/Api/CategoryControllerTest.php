<?php

use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('a category requires a name', function () {
    $response = $this->json('post', '/api/categories', ['categoryName' => null]);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('categoryName');
    $response->assertStatus(422);
});

test('a category cannot have whitespace', function () {
    $response = $this->json('post', '/api/categories', ['categoryName' => 'stephen stack']);

    $response->assertJson(['errors' => true]);
    $response->json(
        ['message' => 'The category name may only contain letters, numbers, dashes and underscores. (and 1 more error)']
    );
    expect($response['errors'])->toHaveKey('categoryName');
    $response->assertStatus(422);
});

test('show single category', function () {
    $category = Category::factory()->create();
    $response = $this->get('/api/categories/' . $category->id);

    $response->assertJson(['categoryName' => $category->categoryName]);
});

test('show single category with command', function () {
    $category = Category::factory()->create();
    $command = Command::factory()->create();

    DB::table('category_command')->insert(
        [
            'command_id' => $command->id,
            'category_id' => $category->id,
        ]
    );

    $this->assertDatabaseHas('category_command', [
        'command_id' => $command->id,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/categories/' . $category->id);

    // dd($response->getContent());
    $response->assertJson(['categoryName' => $category->categoryName]);
    $response->assertJsonFragment(['description' => $command->description]);
});

test('get all categories', function () {
    $category = Category::factory(100)->create();
    $response = $this->get('/api/categories?page=1&perPage=100');
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('get all categories with generic filter', function () {
    $response = $this->get('/api/categories?page=1&perPage=100&filter[categoryName]=switch');
    $response->assertJsonFragment(['categoryName' => 'Switches']);
    $response->assertJsonFragment(['total' => 1]);
    $response->assertStatus(200);
});

test('create category does not require description', function () {
    $category = Category::factory()->make([
        'categoryDescription' => null,
    ]);

    $response = $this->json('post', '/api/categories', $category->toArray());
    $response->assertStatus(200);

    $this->assertDatabaseHas('categories', [
        'categoryName' => $category->categoryName,
        'categoryDescription' => null,
    ]);
});

test('create category', function () {
    $category = Category::factory()->make();
    $response = $this->post('/api/categories', $category->toArray());
    $response->assertStatus(200);

    $this->assertDatabaseHas('categories', [
        'categoryName' => $category->categoryName,
    ]);
});

test('edit category', function () {
    $category = Category::factory()->create();

    $response = $this->json('patch', '/api/categories/' . $category->id, [
        'categoryName' => 'anewcategoryname',
        'categoryDescription' => 'a new categoryDescription name',
        'badgeColor' => 'red',
    ]);
    $response->assertStatus(200);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'categoryName' => 'anewcategoryname',
        'categoryDescription' => 'a new categoryDescription name',
        'badgeColor' => 'red',
    ]);
});

test('delete category', function () {
    $category = Category::factory()->create();

    $this->delete('/api/categories/' . $category->id);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('cannot delete category with existing device relationships', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Cannot delete command group with related devices.');

    $category = Category::factory()->create();
    $device = Device::factory()->create(['device_category_id' => $category->id]);

    // attached category to a device
    $device->category()->attach($category->id);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_category_id' => $category->id]);

    $response = $this->delete('/api/categories/' . $category->id);
    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Cannot delete command group with related devices.']);

    // check again if the category and device still exist
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_category_id' => $category->id]);

    // delete the command and the category and the relationship
    $category->delete();
    $device->delete();
    $device->category()->detach($category->id);
});

test('cannot delete category with existing command relationships', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Cannot delete command group with related commands.');

    $category = Category::factory()->create();
    $command = Command::factory()->create();

    // attached command to a category
    $command->category()->attach($category->id);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('commands', ['id' => $command->id]);

    $response = $this->delete('/api/categories/' . $category->id);
    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Cannot delete command group with related commands.']);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('commands', ['id' => $command->id]);

    // delete the command and the category and the relationship
    $category->delete();
    $command->delete();
    $command->category()->detach($category->id);
});

test('delete many returns error if any category has device relationship', function () {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Cannot delete command group with related devices.');

    $category = Category::factory()->create();
    $category2 = Category::factory()->create();
    $device = Device::factory()->create(['device_category_id' => $category->id]);

    // attached category to a device
    $device->category()->attach($category->id);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_category_id' => $category->id]);

    $response = $this->post('/api/categories/delete-many', ['ids' => [$category->id, $category2->id]]);
    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Cannot delete command group with related devices.']);

    // check again if the category and device still exist
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
    $this->assertDatabaseHas('categories', ['id' => $category2->id]);
    $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_category_id' => $category->id]);

    // delete the command and the category and the relationship
    $category->delete();
    $device->delete();
    $device->category()->detach($category->id);
});

test('get category device relationship but not disabled devices', function () {
    $device = Device::factory()->create(['status' => 100]);
    $category = Category::factory()->create();
    $category->device()->attach($device->id);

    $cat = Category::with('device')->where('id', $category->id)->get();

    expect($cat)->toHaveCount(1);
    expect($cat[0]->device)->toHaveCount(0);
});

afterEach(function () {
    $this->rollBackTransaction();
});
