<?php

use App\Models\RestApiToken;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->templatesDir = storage_path() . '/app/rconfig/templates/';
    $this->canaryPath = storage_path() . '/app/rconfig/template_update_canary.txt';

    File::ensureDirectoryExists($this->templatesDir);
    File::put($this->canaryPath, 'this file lives outside the templates directory');
});

afterEach(function () {
    File::delete($this->canaryPath);

    foreach (File::glob($this->templatesDir . 'template_update_test*') as $leftover) {
        File::delete($leftover);
    }

    $this->rollBackTransaction();
});

/**
 * @return array<string, string>
 */
function payload(string $fileName, string $templateName = 'template_update_test_new'): array
{
    return [
        'templateName' => $templateName,
        'code' => "main:\n  name: probe\n  desc: probe\n",
        'fileName' => $fileName,
    ];
}

function makeTemplateWithFile(): Template
{
    $existing = 'template_update_test_original.yml';
    File::put(storage_path() . '/app/rconfig/templates/' . $existing, 'main:');

    return Template::factory()->create([
        'fileName' => '/app/rconfig/templates/' . $existing,
        'templateName' => 'template_update_test_original',
    ]);
}

test('it does not delete a file outside the templates directory', function () {
    $template = makeTemplateWithFile();

    $this->patchJson('/api/templates/' . $template->id, payload('../template_update_canary.txt'));

    expect(File::exists($this->canaryPath))->toBeTrue('A file outside the templates directory was deleted through the fileName parameter.');
});

test('it does not delete a file via a deep traversal', function () {
    $template = makeTemplateWithFile();

    $this->patchJson(
        '/api/templates/' . $template->id,
        payload('../../../../storage/app/rconfig/template_update_canary.txt')
    );

    expect(File::exists($this->canaryPath))->toBeTrue();
});

test('it rejects a filename containing a traversal sequence', function () {
    $template = makeTemplateWithFile();

    $response = $this->patchJson('/api/templates/' . $template->id, payload('../template_update_canary.txt'));

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('fileName');
});

test('the v1 api is protected by the same check', function () {
    $token = RestApiToken::factory()->create();
    $template = makeTemplateWithFile();

    $this->patchJson(
        '/api/v1/templates/' . $template->id,
        payload('../template_update_canary.txt'),
        ['apitoken' => $token->api_token]
    );

    expect(File::exists($this->canaryPath))->toBeTrue('The v1 API allowed a file outside the templates directory to be deleted.');
});

test('it still replaces the previous template file on a normal update', function () {
    $template = makeTemplateWithFile();
    $originalPath = $this->templatesDir . 'template_update_test_original.yml';

    $response = $this->patchJson(
        '/api/templates/' . $template->id,
        payload('template_update_test_original.yml', 'template_update_test_renamed')
    );

    $response->assertSuccessful();
    expect(File::exists($originalPath))->toBeFalse('The superseded template file was left behind.');
    expect(File::exists($this->templatesDir . 'template_update_test_renamed.yml'))->toBeTrue('The new template file was not written.');
    expect(File::exists($this->canaryPath))->toBeTrue();
});

test('it handles a record with no stored filename', function () {
    $template = Template::factory()->create([
        'fileName' => '',
        'templateName' => 'template_update_test_empty',
    ]);

    $response = $this->patchJson(
        '/api/templates/' . $template->id,
        payload('', 'template_update_test_empty_renamed')
    );

    $response->assertSuccessful();
    expect(File::exists($this->canaryPath))->toBeTrue();
});
