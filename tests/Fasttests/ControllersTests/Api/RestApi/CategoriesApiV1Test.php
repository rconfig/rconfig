<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\Category;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class CategoriesApiV1Test extends TestCase
{
    use TolerantOfTransientDbLocks;

    protected RestApiToken $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    public function test_index_returns_categories(): void
    {
        Category::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/categories?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_category(): void
    {
        $category = Category::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/categories/' . $category->id)
            ->assertStatus(200)
            ->assertJsonFragment(['categoryName' => $category->categoryName]);
    }

    public function test_store_creates_category(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/categories', [
                'categoryName' => 'Core-Switches-API',
                'categoryDescription' => 'Created via REST API test',
                'badgeColor' => 'blue',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('categories', ['categoryName' => 'Core-Switches-API']);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/categories', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['categoryName']);
    }

    public function test_update_edits_category(): void
    {
        $category = Category::factory()->create();

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v1/categories/' . $category->id, [
                'categoryName' => 'Updated-Category-Name',
                'categoryDescription' => 'updated',
                'badgeColor' => 'red',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'categoryName' => 'Updated-Category-Name']);
    }

    public function test_destroy_deletes_category(): void
    {
        $category = Category::factory()->create();

        $this->deleteJsonTolerant('/api/v1/categories/' . $category->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
