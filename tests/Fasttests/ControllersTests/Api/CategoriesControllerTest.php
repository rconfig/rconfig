<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_category_requires_a_name()
    {
        $response = $this->json('post', '/api/categories', ['categoryName' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('categoryName', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_a_category_cannot_have_whitespace()
    {
        $response = $this->json('post', '/api/categories', ['categoryName' => 'stephen stack']);

        $response->assertJson(['errors' => true]);
        $response->json(
            ['message' => 'The category name may only contain letters, numbers, dashes and underscores. (and 1 more error)']
        );
        $this->assertArrayHasKey('categoryName', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_category()
    {
        $category = Category::factory()->create();
        $response = $this->get('/api/categories/' . $category->id);

        $response->assertJson(['categoryName' => $category->categoryName]);
    }

    public function test_show_single_category_with_command()
    {
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
    }

    public function test_get_all_categories()
    {
        $category = Category::factory(100)->create();
        $response = $this->get('/api/categories?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_categories_with_generic_filter()
    {
        $response = $this->get('/api/categories?page=1&perPage=100&filter[categoryName]=switch');
        $response->assertJsonFragment(['categoryName' => 'Switches']);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    public function test_create_category()
    {
        $category = Category::factory()->make();
        $response = $this->post('/api/categories', $category->toArray());
        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'categoryName' => $category->categoryName,
        ]);
    }

    public function test_edit_category()
    {
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
    }

    public function test_delete_category()
    {
        $category = Category::factory()->create();

        $this->delete('/api/categories/' . $category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_existing_device_relationships()
    {
        $this->expectException(\Exception::class);
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
    }

    public function test_cannot_delete_category_with_existing_command_relationships()
    {
        $this->expectException(\Exception::class);
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
    }

    public function test_deleteMany_returns_error_if_any_category_has_device_relationship()
    {
        $this->expectException(\Exception::class);
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
    }

    public function test_get_category_device_relationship_but_not_disabled_devices()
    {
        $device = Device::factory()->create(['status' => 100]);
        $category = Category::factory()->create();
        $category->device()->attach($device->id);

        $cat = Category::with('device')->where('id', $category->id)->get();

        $this->assertCount(1, $cat);
        $this->assertCount(0, $cat[0]->device);
    }
}
