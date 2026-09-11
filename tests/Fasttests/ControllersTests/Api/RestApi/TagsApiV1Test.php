<?php

use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function tagsApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns tags', function () {
    Tag::factory(3)->create();

    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/tags?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns tag', function () {
    $tag = Tag::factory()->create();

    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/tags/' . $tag->id)
        ->assertStatus(200)
        ->assertJsonFragment(['tagname' => $tag->tagname]);
});

test('store creates tag', function () {
    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/tags', [
            'tagname' => 'Site-USA-API',
            'tagDescription' => 'Created via REST API test',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('tags', ['tagname' => 'Site-USA-API']);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/tags', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['tagname']);
});

test('update edits tag', function () {
    $tag = Tag::factory()->create();

    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/tags/' . $tag->id, [
            'tagname' => 'a-new-tag-name-api',
            'tagDescription' => 'updated',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('tags', ['id' => $tag->id, 'tagname' => 'a-new-tag-name-api']);
});

test('destroy deletes tag', function () {
    $tag = Tag::factory()->create();

    $this->withHeaders(tagsApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/tags/' . $tag->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});
