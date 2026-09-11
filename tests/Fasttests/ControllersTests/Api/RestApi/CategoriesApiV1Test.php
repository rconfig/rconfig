<?php

use App\Models\Category;
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
function categoriesApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns categories', function () {
    Category::factory(3)->create();

    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/categories?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns category', function () {
    $category = Category::factory()->create();

    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/categories/' . $category->id)
        ->assertStatus(200)
        ->assertJsonFragment(['categoryName' => $category->categoryName]);
});

test('store creates category', function () {
    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->postJson('/api/v1/categories', [
            'categoryName' => 'Core-Switches-API',
            'categoryDescription' => 'Created via REST API test',
            'badgeColor' => 'blue',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('categories', ['categoryName' => 'Core-Switches-API']);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->postJson('/api/v1/categories', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['categoryName']);
});

test('update edits category', function () {
    $category = Category::factory()->create();

    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/categories/' . $category->id, [
            'categoryName' => 'Updated-Category-Name',
            'categoryDescription' => 'updated',
            'badgeColor' => 'red',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'categoryName' => 'Updated-Category-Name']);
});

test('destroy deletes category', function () {
    $category = Category::factory()->create();

    $this->withHeaders(categoriesApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/categories/' . $category->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});
