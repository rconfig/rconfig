<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Category;
use App\Models\Command;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function a_category_requires_a_name()
    {
        $response = $this->json('post', '/api/categories', ['categoryName' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('categoryName', $response['errors']);
        $response->assertStatus(422);
    }

    /** @test */
    public function a_category_cannot_have_whitespace()
    {
        $response = $this->json('post', '/api/categories', ['categoryName' => 'stephen stack']);

        $response->assertJson(['errors' => true]);
        $response->json(
            ['message' => 'The category name may only contain letters, numbers, dashes and underscores. (and 1 more error)']
        );
        $this->assertArrayHasKey('categoryName', $response['errors']);
        $response->assertStatus(422);
    }

    /** @test */
    public function show_single_category()
    {
        $category = Category::factory()->create();
        $response = $this->get('/api/categories/'.$category->id);

        $response->assertJson(['categoryName' => $category->categoryName]);
    }

    /** @test */
    public function show_single_category_with_command()
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

        $response = $this->get('/api/categories/'.$category->id);
        // dd($response->getContent());

        $response->assertJson(['categoryName' => $category->categoryName]);
        $response->assertJsonFragment(['description' => $command->description]);
    }

    /** @test */
    public function get_all_categories()
    {
        $category = \App\Models\Category::factory(100)->create();
        $response = $this->get('/api/categories?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    /** @test */
    public function get_all_categories_with_generic_filter()
    {
        $response = $this->get('/api/categories?page=1&perPage=100&filter=Switches');
        $response->assertJsonFragment(['categoryName' => 'Switches']);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    /** @test */
    public function create_category()
    {
        $category = \App\Models\Category::factory()->create();
        $this->post('/api/categories', $category->toArray());

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'categoryName' => $category->categoryName,
        ]);
    }

    /** @test */
    public function edit_category()
    {
        $category = \App\Models\Category::factory()->create();

        $response = $this->json('patch', '/api/categories/'.$category->id, [
            'categoryName' => 'anewcategoryname',
            'categoryDescription' => 'a new categoryDescription name',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'categoryName' => 'anewcategoryname',
            'categoryDescription' => 'a new categoryDescription name',
        ]);
    }

    /** @test */
    public function delete_category()
    {
        $category = \App\Models\Category::factory()->create();

        $this->delete('/api/categories/'.$category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
