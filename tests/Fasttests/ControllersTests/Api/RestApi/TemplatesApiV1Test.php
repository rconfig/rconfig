<?php

use App\Models\RestApiToken;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->createdFiles = [];

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

afterEach(function () {
    // Clean up any template files written to the templates directory.
    foreach ($this->createdFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }

});

/**
 * @return array<string, string>
 */
function templatesApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns templates', function () {
    Template::factory(3)->create();

    $this->withHeaders(templatesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/templates?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('store show and destroy template', function () {
    $templateName = 'rest_api_test_template_' . uniqid();
    $code = "vendor: cisco\nmodel: test\ncommands:\n  - show running-config\n";

    // Store writes a real .yml file to the templates directory.
    $this->withHeaders(templatesApiV1AuthHeader($this->token))
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
    $this->withHeaders(templatesApiV1AuthHeader($this->token))
        ->getJson('/api/v1/templates/' . $template->id)
        ->assertStatus(200)
        ->assertJsonFragment(['templateName' => $templateName]);

    // Destroy removes the record and the file.
    $this->withHeaders(templatesApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/templates/' . $template->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('templates', ['id' => $template->id]);
});

test('destroy factory template without file', function () {
    // Factory templates have no file on disk; destroy tolerates this gracefully.
    $template = Template::factory()->create();

    $this->withHeaders(templatesApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/templates/' . $template->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('templates', ['id' => $template->id]);
});

// Note: store/update are also exercised by the SPA TemplatesControllerTest.
// Here we cover read + store + destroy via the token-authenticated API.
