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
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_command_requires_a_name_and_a_category_array()
    {
        $response = $this->json('post', '/api/commands', ['command' => null]);

        $response->assertJson(['errors' => true]);
        $response->assertJsonFragment(['command' => ['The command field is required.']]);
        $response->assertJsonFragment(['categoryArray' => ['The category array field is required.']]);
        $this->assertArrayHasKey('command', $response['errors']);
        $response->assertStatus(422);
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
        $response = $this->getJson('/api/commands?page=1&perPage=100&q=show clock');
        $response->assertStatus(200);

        $response->assertJsonFragment(['command' => 'show clock']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'command',
                    'category',
                ],
            ],
            'total',
            'current_page',
            'per_page',
        ]);

        $this->assertGreaterThan(5, $response['total']);
    }

    public function test_create_command_with_category_array()
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

        $response = $this->json('post', '/api/commands', [
            'command' => $command->command,
            'description' => $command->description,
            'categoryArray' => $categories->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $insertedComand = Command::where('command', $command->command)->first();

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

        $this->assertDatabaseHas('categories', [
            'id' => $categories[0]->id,
            'categoryName' => $categories[0]->categoryName,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categories[1]->id,
            'categoryName' => $categories[1]->categoryName,
        ]);

        $response = $this->json('post', '/api/commands', [
            'command' => $command->command,
            'description' => $command->description,
            'categoryArray' => $categories->pluck('id')->toArray(),
        ]);
        $response->assertStatus(200);
        $insertedComand = Command::where('command', $command->command)->first();

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
            'categoryArray' => $categories2->pluck('id')->toArray(),
        ]);
        $response->assertStatus(200);

        $response = $this->json('PATCH', '/api/commands/' . $insertedComand->id, [
            'command' => 'a new command name',
            'description' => 'a new description name',
            'categoryArray' => $categories2->pluck('id')->toArray(),
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

    public function test_cannot_delete_command_with_existing_category_relationships()
    {
        $this->markTestSkipped('We should be able to delete downstream items.');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete command with related categories.');

        $command = Command::factory()->create();
        // attached command to a category
        $category = Category::factory()->create();
        $command->Category()->attach($category->id);

        $this->assertDatabaseHas('commands', ['id' => $command->id]);

        $response = $this->delete('/api/commands/' . $command->id);
        $response->assertStatus(500);
        $response->assertJsonFragment(['message' => 'Cannot delete command with related categories.']);

        $this->assertDatabaseHas('commands', ['id' => $command->id]);

        // delete the command and the category and the relationship
        $command->Category()->detach();
        $command->delete();
        $category->delete();
    }

    public function test_can_bulk_update_categories()
    {

        $command = Command::factory(4)->create();
        $categories = Category::factory(3)->create();

        $this->assertDatabaseMissing('category_command', [
            'command_id' => $command[0]->id,
            'category_id' => $categories[0]->id,
        ]);

        $this->assertDatabaseMissing('category_command', [
            'command_id' => $command[1]->id,
            'category_id' => $categories[1]->id,
        ]);

        // first check error response
        $response = $this->json('POST', '/api/commands/bulk-update-categories', [
            'commands' => [],
            'categories' => [],
        ]);

        $response->assertStatus(422);

        // then check pass
        $response = $this->json('POST', '/api/commands/bulk-update-categories', [
            'commands' => $command,
            'categories' => $categories,
        ]);

        $response->assertStatus(200);

        foreach ($command as $cmd) {
            $this->assertDatabaseHas('category_command', [
                'command_id' => $cmd->id,
                'category_id' => $categories[0]->id,
            ]);
        }
        foreach ($command as $cmd) {
            $this->assertDatabaseHas('category_command', [
                'command_id' => $cmd->id,
                'category_id' => $categories[1]->id,
            ]);
        }

        foreach ($command as $cmd) {
            $this->assertDatabaseHas('category_command', [
                'command_id' => $cmd->id,
                'category_id' => $categories[2]->id,
            ]);
        }

        // remove the commmands and categories
        foreach ($command as $cmd) {
            $cmd->delete();
        }
        foreach ($categories as $cat) {
            $cat->delete();
        }
    }

    protected function tearDown(): void
    {

        $this->rollBackTransaction();
        parent::tearDown();
    }
}
