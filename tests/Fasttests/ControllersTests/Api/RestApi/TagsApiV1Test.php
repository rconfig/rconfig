<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\Tag;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class TagsApiV1Test extends TestCase
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

    public function test_index_returns_tags(): void
    {
        Tag::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/tags?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/tags/' . $tag->id)
            ->assertStatus(200)
            ->assertJsonFragment(['tagname' => $tag->tagname]);
    }

    public function test_store_creates_tag(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/tags', [
                'tagname' => 'Site-USA-API',
                'tagDescription' => 'Created via REST API test',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('tags', ['tagname' => 'Site-USA-API']);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/tags', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tagname']);
    }

    public function test_update_edits_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v1/tags/' . $tag->id, [
                'tagname' => 'a-new-tag-name-api',
                'tagDescription' => 'updated',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'tagname' => 'a-new-tag-name-api']);
    }

    public function test_destroy_deletes_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->deleteJsonTolerant('/api/v1/tags/' . $tag->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
