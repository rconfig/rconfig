<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Category;
use App\Models\Command;
use App\Models\User;
use Tests\TestCase;

class CommandsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_command_requires_a_name_and_a_category_array()
    {
        $response = $this->json('post', '/api/commands', ['command' => null]);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['command', 'category']);
    }

    public function test_show_single_command()
    {
        $command = Command::factory()->create();
        $response = $this->get('/api/commands/' . $command->id);
        $response->assertStatus(200);

        $response->assertJson(['command' => $command->command]);
    }

    public function test_show_single_command_with_categoryArray()
    {
        $category = Category::factory()->create();
        $command = Command::factory()->create();

        \DB::table('category_command')->insert(
            [
                'command_id' => $command->id,
                'category_id' => $category->id,
            ]
        );

        $this->assertDatabaseHas('category_command', [
            'command_id' => $command->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get('/api/commands/' . $command->id);
        // dd($response->getContent());

        $response->assertJson(['command' => $command->command]);
        $response->assertJsonFragment(['description' => $command->description]);
        $response->assertJsonFragment(['categoryName' => $category->categoryName]);
    }

    public function test_get_all_commands()
    {
        Command::factory(100)->create();
        $response = $this->get('/api/commands?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_commands_with_filter()
    {
        $response = $this->get('/api/commands?page=1&perPage=100&filter[command]=show clock');
        $response->assertStatus(200);

        $response->assertJsonFragment(['command' => 'show clock']);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    public function test_create_command_with_categoryArray()
    {
        $command = Command::factory()->make(); // do not persist to DB
        $categories = Category::factory(3)->create();

        $this->assertDatabaseHas('categories', [
            'id' => $categories[0]->id,
            'categoryName' => $categories[0]->categoryName,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $categories[1]->id,
            'categoryName' => $categories[1]->categoryName,
        ]);
        // dd(\DB::table('categories')->get());

        $response = $this->json('post', '/api/commands', [
            'command' => $command->command,
            'description' => $command->description,
            'category' => $categories,
        ]);
        $response->assertStatus(200);

        $insertedComand = Command::where('command', $command->command)->first();

        $response->assertStatus(200);

        $this->assertDatabaseHas('category_command', [
            'command_id' => $insertedComand->id,
            'category_id' => $categories[0]->id,
        ]);

        $this->assertDatabaseHas('commands', [
            'command' => $command->command,
            'description' => $command->description,
        ]);
    }

    public function test_edit_command()
    {
        $command = Command::factory()->make(); // do not persist to DB
        $categories = Category::factory(3)->create();
        // dd($categories);

        $this->assertDatabaseHas('categories', [
            'id' => $categories[0]->id,
            'categoryName' => $categories[0]->categoryName,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categories[1]->id,
            'categoryName' => $categories[1]->categoryName,
        ]);
        // dd(\DB::table('categories')->get());

        $response = $this->json('post', '/api/commands', [
            'command' => $command->command,
            'description' => $command->description,
            'category' => $categories,
        ]);
        $insertedComand = Command::where('command', $command->command)->first();

        $response->assertStatus(200);

        $this->assertDatabaseHas('category_command', [
            'command_id' => $insertedComand->id,
            'category_id' => $categories->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('commands', [
            'command' => $command->command,
            'description' => $command->description,
        ]);

        $categories2 = Category::factory(3)->create();
        // now apply the update
        $response = $this->json('PATCH', '/api/commands/' . $insertedComand->id, [
            'command' => 'new command',
            'description' => 'new description',
            'category' => $categories2,
        ]);
        $response = $this->json('PATCH', '/api/commands/' . $insertedComand->id, [
            'command' => 'a new command name',
            'description' => 'a new description name',
            'category' => $categories2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Command edited successfully!']);

        $this->assertDatabaseHas('category_command', [
            'command_id' => $insertedComand->id,
            'category_id' => $categories2->pluck('id')->first(),
        ]);

        $this->assertDatabaseHas('commands', [
            'id' => $insertedComand->id,
            'command' => 'a new command name',
            'description' => 'a new description name',
        ]);
    }

    public function test_delete_command()
    {
        $command = Command::factory()->create();
        $this->assertDatabaseHas('commands', ['id' => $command->id]);

        $this->delete('/api/commands/' . $command->id);

        $this->assertDatabaseMissing('commands', ['id' => $command->id]);
    }
}
