<?php

use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->actingAs(User::factory()->create());

    $suffix = bin2hex(random_bytes(6));
    $templatesDir = storage_path('app/rconfig/templates');
    $oldFormat = file_get_contents(base_path('tests/storage/templates/oldformat.yml'));

    $this->templateName = "reformat-endpoint-{$suffix}.yml";
    $this->yamlTemplateName = "reformat-endpoint-{$suffix}.yaml";
    $this->textName = "reformat-endpoint-{$suffix}.txt";
    $this->linkName = "reformat-endpoint-link-{$suffix}.yml";
    $this->outsideName = "reformat-endpoint-outside-{$suffix}.yml";
    $this->outsidePath = storage_path('app/rconfig/' . $this->outsideName);

    $this->createdPaths = [
        $templatesDir . '/' . $this->templateName,
        $templatesDir . '/' . $this->yamlTemplateName,
        $templatesDir . '/' . $this->textName,
        $templatesDir . '/' . $this->linkName,
        $this->outsidePath,
    ];

    file_put_contents($templatesDir . '/' . $this->templateName, $oldFormat);
    file_put_contents($templatesDir . '/' . $this->yamlTemplateName, $oldFormat);
    file_put_contents($templatesDir . '/' . $this->textName, $oldFormat);
    file_put_contents($this->outsidePath, $oldFormat);
    symlink($this->outsidePath, $templatesDir . '/' . $this->linkName);
});

afterEach(function () {
    foreach ($this->createdPaths as $path) {
        if (is_link($path) || is_file($path)) {
            unlink($path);
        }
    }

    $this->rollBackTransaction();
});

test('reformats a template in the templates directory', function () {
    $this->postJson('/api/reformat-template', ['fileName' => $this->templateName])
        ->assertStatus(200)
        ->assertSee('name: "SSH Private Key Template"', false);
});

test('reformats a template with a yaml extension', function () {
    $this->postJson('/api/reformat-template', ['fileName' => $this->yamlTemplateName])
        ->assertStatus(200)
        ->assertSee('name: "SSH Private Key Template"', false);
});

test('only reads template files from inside the templates directory', function (string $fileName) {
    $this->postJson('/api/reformat-template', ['fileName' => $fileName])
        ->assertStatus(404)
        ->assertExactJson(['success' => false, 'message' => 'Template not found'])
        ->assertDontSee(storage_path(), false);
})->with([
    'relative parent path' => [fn () => '../' . $this->outsideName],
    'absolute path' => [fn () => $this->outsidePath],
    'encoded parent path' => [fn () => '..%2F' . $this->outsideName],
    'link to a file outside the directory' => [fn () => $this->linkName],
    'non yaml file in the directory' => [fn () => $this->textName],
    'non yaml file outside the directory' => [fn () => '../../../../.env'],
    'missing file' => [fn () => 'reformat-endpoint-missing.yml'],
    'empty name' => [fn () => ''],
]);

test('a missing template and a file outside the directory get the same response', function () {
    $missing = $this->postJson('/api/reformat-template', ['fileName' => 'reformat-endpoint-missing.yml']);
    $outside = $this->postJson('/api/reformat-template', ['fileName' => '../' . $this->outsideName]);

    expect($outside->status())->toBe($missing->status());
    expect($outside->getContent())->toBe($missing->getContent());
});
