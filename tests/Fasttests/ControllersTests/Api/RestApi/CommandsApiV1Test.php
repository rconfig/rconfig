<?php

use App\Models\Category;
use App\Models\Command;
use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function commandsApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns commands', function () {
    Command::factory(3)->create();

    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/commands?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns command', function () {
    $command = Command::factory()->create();

    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/commands/' . $command->id)
        ->assertStatus(200)
        ->assertJsonFragment(['command' => $command->command]);
});

test('store creates command', function () {
    $category = Category::factory()->create();

    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/commands', [
            'command' => 'show running-config api-test',
            'description' => 'Created via REST API test',
            'categoryArray' => [$category->id],
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('commands', ['command' => 'show running-config api-test']);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/commands', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['command', 'categoryArray']);
});

test('update edits command', function () {
    $category = Category::factory()->create();
    $command = Command::factory()->create();

    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/commands/' . $command->id, [
            'command' => 'show version api-updated',
            'description' => 'updated',
            'categoryArray' => [$category->id],
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('commands', ['id' => $command->id, 'command' => 'show version api-updated']);
});

test('destroy deletes command', function () {
    $command = Command::factory()->create();

    $this->withHeaders(commandsApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/commands/' . $command->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('commands', ['id' => $command->id]);
});
