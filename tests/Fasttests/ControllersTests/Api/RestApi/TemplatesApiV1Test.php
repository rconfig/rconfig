<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class TemplatesApiV1Test extends TestCase
{
    use TolerantOfTransientDbLocks;

    protected RestApiToken $token;

    /** @var array<int, string> */
    private array $createdFiles = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
    }

    protected function tearDown(): void
    {
        // Clean up any template files written to the templates directory.
        foreach ($this->createdFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    public function test_index_returns_templates(): void
    {
        Template::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/templates?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_store_show_and_destroy_template(): void
    {
        $templateName = 'rest_api_test_template_' . uniqid();
        $code = "vendor: cisco\nmodel: test\ncommands:\n  - show running-config\n";

        // Store writes a real .yml file to the templates directory.
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/templates', [
                'templateName' => $templateName,
                'code' => $code,
                'description' => 'Created via REST API test',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $template = Template::where('templateName', $templateName)->firstOrFail();
        $this->createdFiles[] = storage_path() . $template->fileName;

        // Show reads the file contents back from disk.
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/templates/' . $template->id)
            ->assertStatus(200)
            ->assertJsonFragment(['templateName' => $templateName]);

        // Destroy removes the record and the file.
        $this->deleteJsonTolerant('/api/v1/templates/' . $template->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('templates', ['id' => $template->id]);
    }

    public function test_destroy_factory_template_without_file(): void
    {
        // Factory templates have no file on disk; destroy tolerates this gracefully.
        $template = Template::factory()->create();

        $this->deleteJsonTolerant('/api/v1/templates/' . $template->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('templates', ['id' => $template->id]);
    }

    // Note: store/update are also exercised by the SPA TemplatesControllerTest.
    // Here we cover read + store + destroy via the token-authenticated API.
}
