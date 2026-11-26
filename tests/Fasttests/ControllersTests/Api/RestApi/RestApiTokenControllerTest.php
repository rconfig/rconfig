<?php

use App\Http\Requests\StoreRestApiTokenRequest;
use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create(['role' => 'Admin']);
    $this->actingAs($this->user);
});

test('a User account cannot list tokens', function () {
    RestApiToken::factory()->create(['api_token_name' => 'hidden-from-users']);

    $this->actingAs(User::factory()->create(['role' => 'User']));

    $this->getJson('/api/settings/rest-api-token')
        ->assertStatus(403)
        ->assertJsonMissing(['api_token_name' => 'hidden-from-users']);
});

test('a User account cannot create a token', function () {
    $this->actingAs(User::factory()->create(['role' => 'User']));

    $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'user-made-token'])
        ->assertStatus(403);

    $this->assertDatabaseMissing('rest_api_tokens', ['api_token_name' => 'user-made-token']);
});

test('a User account cannot delete a token', function () {
    $token = RestApiToken::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'User']));

    $this->deleteJson('/api/settings/rest-api-token/' . $token->id)
        ->assertStatus(403);

    $this->assertDatabaseHas('rest_api_tokens', ['id' => $token->id]);
});

test('an account with an unrecognised role cannot create a token', function () {
    $this->actingAs(User::factory()->create(['role' => 'Viewer']));

    $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'viewer-token'])
        ->assertStatus(403);

    $this->assertDatabaseMissing('rest_api_tokens', ['api_token_name' => 'viewer-token']);
});

test('the Admin role is matched case insensitively', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'lowercase-admin-token'])
        ->assertStatus(200);

    $this->assertDatabaseHas('rest_api_tokens', ['api_token_name' => 'lowercase-admin-token']);
});

test('the create request rejects a User account without the route middleware', function () {
    $request = new StoreRestApiTokenRequest;

    $request->setUserResolver(fn () => User::factory()->make(['role' => 'User']));
    expect($request->authorize())->toBeFalse();

    $request->setUserResolver(fn () => null);
    expect($request->authorize())->toBeFalse();

    $request->setUserResolver(fn () => User::factory()->make(['role' => 'Admin']));
    expect($request->authorize())->toBeTrue();
});

test('create returns plaintext token once', function () {
    $response = $this->postJson('/api/settings/rest-api-token', [
        'api_token_name' => 'my-automation',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'api_token_name', 'api_token']]);

    $plaintext = $response->json('data.api_token');
    expect($plaintext)->not->toBeEmpty();
    $this->assertDatabaseHas('rest_api_tokens', ['api_token_name' => 'my-automation']);
});

test('index never exposes the raw token', function () {
    $token = RestApiToken::factory()->create(['api_token_name' => 'listed-token']);

    $response = $this->getJson('/api/settings/rest-api-token');

    $response->assertStatus(200)
        ->assertJsonFragment(['api_token_name' => 'listed-token']);

    $payload = $response->json('data');
    foreach ($payload as $row) {
        $this->assertArrayNotHasKey('api_token', $row);
    }

    $this->assertStringNotContainsString($token->api_token, $response->getContent());
});

test('duplicate name is rejected', function () {
    RestApiToken::factory()->create(['api_token_name' => 'dupe']);

    $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'dupe'])
        ->assertStatus(422);
});

test('delete removes token', function () {
    $token = RestApiToken::factory()->create();

    $this->deleteJson('/api/settings/rest-api-token/' . $token->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('rest_api_tokens', ['id' => $token->id]);
});
