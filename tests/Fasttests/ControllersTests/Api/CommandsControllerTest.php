<?php

use App\Models\Category;
use App\Models\Command;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('a command requires a name and a category array', function () {
    $response = $this->json('post', '/api/commands', ['command' => null]);

    $response->assertJson(['errors' => true]);
    $response->assertJsonFragment(['command' => ['The command field is required.']]);
    $response->assertJsonFragment(['categoryArray' => ['The category array field is required.']]);
    expect($response['errors'])->toHaveKey('command');
    $response->assertStatus(422);
});

test('show single command', function () {
    $command = Command::factory()->create();
    $response = $this->get('/api/commands/' . $command->id);
    $response->assertStatus(200);

    $response->assertJson(['command' => $command->command]);
});

test('show single command with category array', function () {
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

    $response = $this->get('/api/commands/' . $command->id);

    // dd($response->getContent());
    $response->assertJson(['command' => $command->command]);
    $response->assertJsonFragment(['description' => $command->description]);
    $response->assertJsonFragment(['categoryName' => $category->categoryName]);
});

test('get all commands', function () {
    Command::factory(100)->create();
    $response = $this->get('/api/commands?page=1&perPage=100');
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('get all commands with filter', function () {
    Command::factory(10)->create();

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

    expect($response['total'])->toBeGreaterThan(5);
});

test('create command with category array', function () {
    $command = Command::factory()->make();
    // do not persist to DB
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
});

test('edit command', function () {
    $command = Command::factory()->make();
    // do not persist to DB
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
});

test('delete command', function () {
    $command = Command::factory()->create();
    $this->assertDatabaseHas('commands', ['id' => $command->id]);

    $this->delete('/api/commands/' . $command->id);

    $this->assertDatabaseMissing('commands', ['id' => $command->id]);
});

test('cannot delete command with existing category relationships', function () {
    $this->markTestSkipped('We should be able to delete downstream items.');
    $this->expectException(Exception::class);
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
});

test('can bulk update categories', function () {
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
});

afterEach(function () {
    $this->rollBackTransaction();
});
