<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\RestApiToken;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Cover for the file handling in TemplateController::update().
 *
 * Updating a template deletes the file the record currently points at. That name must
 * come from the stored record, never from the request, otherwise a caller can nominate
 * any file on disk for deletion. The same method is inherited by the token authenticated
 * v1 API, so both routes are exercised here.
 */
class TemplateUpdateFilePathTest extends TestCase
{
    protected User $user;
    private string $templatesDir;
    private string $canaryPath;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->templatesDir = storage_path() . '/app/rconfig/templates/';
        $this->canaryPath = storage_path() . '/app/rconfig/template_update_canary.txt';

        File::ensureDirectoryExists($this->templatesDir);
        File::put($this->canaryPath, 'this file lives outside the templates directory');
    }

    protected function tearDown(): void
    {
        File::delete($this->canaryPath);

        foreach (File::glob($this->templatesDir . 'template_update_test*') as $leftover) {
            File::delete($leftover);
        }

        $this->rollBackTransaction();
        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function payload(string $fileName, string $templateName = 'template_update_test_new'): array
    {
        return [
            'templateName' => $templateName,
            'code' => "main:\n  name: probe\n  desc: probe\n",
            'fileName' => $fileName,
        ];
    }

    private function makeTemplateWithFile(): Template
    {
        $existing = 'template_update_test_original.yml';
        File::put($this->templatesDir . $existing, 'main:');

        return Template::factory()->create([
            'fileName' => '/app/rconfig/templates/' . $existing,
            'templateName' => 'template_update_test_original',
        ]);
    }

    public function test_it_does_not_delete_a_file_outside_the_templates_directory(): void
    {
        $template = $this->makeTemplateWithFile();

        $this->patchJson('/api/templates/' . $template->id, $this->payload('../template_update_canary.txt'));

        $this->assertTrue(
            File::exists($this->canaryPath),
            'A file outside the templates directory was deleted through the fileName parameter.'
        );
    }

    public function test_it_does_not_delete_a_file_via_a_deep_traversal(): void
    {
        $template = $this->makeTemplateWithFile();

        $this->patchJson(
            '/api/templates/' . $template->id,
            $this->payload('../../../../storage/app/rconfig/template_update_canary.txt')
        );

        $this->assertTrue(File::exists($this->canaryPath));
    }

    public function test_it_rejects_a_filename_containing_a_traversal_sequence(): void
    {
        $template = $this->makeTemplateWithFile();

        $response = $this->patchJson('/api/templates/' . $template->id, $this->payload('../template_update_canary.txt'));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('fileName');
    }

    public function test_the_v1_api_is_protected_by_the_same_check(): void
    {
        $token = RestApiToken::factory()->create();
        $template = $this->makeTemplateWithFile();

        $this->patchJson(
            '/api/v1/templates/' . $template->id,
            $this->payload('../template_update_canary.txt'),
            ['apitoken' => $token->api_token]
        );

        $this->assertTrue(
            File::exists($this->canaryPath),
            'The v1 API allowed a file outside the templates directory to be deleted.'
        );
    }

    public function test_it_still_replaces_the_previous_template_file_on_a_normal_update(): void
    {
        $template = $this->makeTemplateWithFile();
        $originalPath = $this->templatesDir . 'template_update_test_original.yml';

        $response = $this->patchJson(
            '/api/templates/' . $template->id,
            $this->payload('template_update_test_original.yml', 'template_update_test_renamed')
        );

        $response->assertSuccessful();
        $this->assertFalse(File::exists($originalPath), 'The superseded template file was left behind.');
        $this->assertTrue(
            File::exists($this->templatesDir . 'template_update_test_renamed.yml'),
            'The new template file was not written.'
        );
        $this->assertTrue(File::exists($this->canaryPath));
    }

    public function test_it_handles_a_record_with_no_stored_filename(): void
    {
        $template = Template::factory()->create([
            'fileName' => '',
            'templateName' => 'template_update_test_empty',
        ]);

        $response = $this->patchJson(
            '/api/templates/' . $template->id,
            $this->payload('', 'template_update_test_empty_renamed')
        );

        $response->assertSuccessful();
        $this->assertTrue(File::exists($this->canaryPath));
    }
}
