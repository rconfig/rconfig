<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->templatesDstDir = templates_path() . 'rConfig-templates';
});

test('github connectivity test', function () {
    $response = $this->json('GET', '/api/test-template-repo-connection');

    $response->assertStatus(200)->assertJsonFragment([
        // The templates repo's default branch is now main, not master.
        'html_url' => 'https://github.com/rconfig/rConfig-templates/blob/main/.gitignore',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'msg' => 'Successfully connected to rConfig Templates Github repo',
    ]);
});

test('failed github connectivity test', function () {
    Config::set('github.git.rconfig-template-repo', '123');
    expect(Config::get('github.git.rconfig-template-repo'))->toEqual('123');

    $response = $this->json('GET', '/api/test-template-repo-connection');
    $this->assertStringContainsString('Exception thrown: Could not connect to repo - HTTP request returned status code 404', $response->json()['message']['msg']);
});

test('storage has github clone', function () {
    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }

    Artisan::call('rconfig:clone-templates');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    expect(count($arr))->toBeGreaterThan(0);

    $response = $this->json('GET', '/api/list-template-repo-folders');

    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/brocade',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/checkpoint',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/sonicwall',
    ]);

    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }
});

test('storage does not have github clone', function () {
    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }
    $response = $this->json('GET', '/api/list-template-repo-folders');

    $response->assertStatus(422)->assertJsonFragment([
        'msg' => 'rConfig-templates is empty, or does not exist. Clone from "https://github.com/rconfig/rconfig-templates" may have failed! Try importing the templates again.!',
    ]);
});

test('can get list of dirs', function () {
    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }

    Artisan::call('rconfig:clone-templates');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    expect(count($arr))->toBeGreaterThan(0);

    $response = $this->json('GET', '/api/list-template-repo-folders');
    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/brocade',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/checkpoint',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/sonicwall',
    ]);

    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }
});

test('given dir can get list of files', function () {
    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }

    Artisan::call('rconfig:clone-templates');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    expect(count($arr))->toBeGreaterThan(0);

    $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco']);

    $response->assertStatus(200)->assertJsonFragment([
        rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco/cisco-asa-ssh-enable.yml',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco/cisco-wlc-ssh-noenable.yml',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco/cisco-ios-ssh-noenable.yml',
    ]);

    // has readme.md
    $response->assertStatus(200)->assertJsonFragment([
        rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco/README.md',
    ]);

    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }
});

test('given file can be read', function () {
    Artisan::call('rconfig:clone-templates');

    $response = $this->json('POST', '/api/get-template-file-contents', ['filepath' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/cisco/cisco-asa-ssh-enable.yml']);

    $response->assertStatus(200)->assertJsonFragment([
        'templateName' => 'Cisco ASA - SSH - Enable',
    ]);
    $response->assertStatus(200)->assertJsonFragment([
        'description' => 'Cisco ASA SSH based connection with enable mode',
    ]);

    if (is_dir($this->templatesDstDir)) {
        File::deleteDirectory($this->templatesDstDir);
    }
});

/*
 * Containment cover for the two endpoints that take a path from the caller.
 *
 * These deliberately do not clone the repository: they build a small fixture locally, so
 * they do not inherit the network dependency of the tests above.
 *
 * The bait matters. Pointing these at /etc/passwd would prove nothing, because the
 * unhardened read already fails on such a file: it is not a YAML mapping, so the parse
 * blows up before anything is returned. The planted files below are valid rConfig
 * templates sitting outside the repository, which the unhardened code serves at 200.
 */
const LEAK_MARKER = 'LEAKED_TEMPLATE_NAME';

function fixtureVendorDir(): string
{
    return templates_path() . 'rConfig-templates' . '/ContainmentTestVendor';
}

function baitFile(): string
{
    return export_path() . 'containment_probe.yml';
}

function baitDir(): string
{
    return export_path() . 'containment_probe_dir';
}

function plantFixtureAndBait(): void
{
    $template = "main:\n  name: " . LEAK_MARKER . "\n  desc: planted template\n";

    File::ensureDirectoryExists(fixtureVendorDir());
    File::put(fixtureVendorDir() . '/valid.yml', "main:\n  name: Contained Template\n  desc: inside the repo\n");

    File::ensureDirectoryExists(baitDir());
    File::put(baitFile(), $template);
    File::put(baitDir() . '/probe.yml', $template);
}

function removeFixtureAndBait(): void
{
    File::delete(baitFile());
    File::deleteDirectory(baitDir());

    $templatesDstDir = templates_path() . 'rConfig-templates';
    if (is_dir($templatesDstDir)) {
        File::deleteDirectory($templatesDstDir);
    }
}

test('it lists yml files inside the cloned repo', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => fixtureVendorDir()]);

    $response->assertStatus(200);
    $this->assertStringContainsString('valid.yml', $response->getContent());

    removeFixtureAndBait();
});

test('it refuses to list a directory outside the cloned repo', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => baitDir()]);

    $response->assertStatus(422);
    $this->assertStringNotContainsString('probe.yml', $response->getContent());

    removeFixtureAndBait();
});

test('it refuses to list a directory reached by traversal', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/list-repo-folders-contents', [
        'directory' => fixtureVendorDir() . '/../../../exports/containment_probe_dir',
    ]);

    $response->assertStatus(422);
    $this->assertStringNotContainsString('probe.yml', $response->getContent());

    removeFixtureAndBait();
});

test('it refuses a missing directory parameter', function () {
    $response = $this->json('POST', '/api/list-repo-folders-contents', []);

    $response->assertStatus(422);
    $this->assertArrayNotHasKey('data', $response->json());
});

test('it reads a template inside the cloned repo', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/get-template-file-contents', [
        'filepath' => fixtureVendorDir() . '/valid.yml',
    ]);

    $response->assertStatus(200)->assertJsonFragment(['templateName' => 'Contained Template']);

    removeFixtureAndBait();
});

test('it refuses to read a template outside the cloned repo', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/get-template-file-contents', ['filepath' => baitFile()]);

    $response->assertStatus(422);
    $this->assertStringNotContainsString(LEAK_MARKER, $response->getContent());

    removeFixtureAndBait();
});

test('it refuses to read a template reached by traversal', function () {
    plantFixtureAndBait();

    $response = $this->json('POST', '/api/get-template-file-contents', [
        'filepath' => fixtureVendorDir() . '/../../../exports/containment_probe.yml',
    ]);

    $response->assertStatus(422);
    $this->assertStringNotContainsString(LEAK_MARKER, $response->getContent());

    removeFixtureAndBait();
});

test('it refuses a non yaml file inside the cloned repo', function () {
    plantFixtureAndBait();
    File::put(fixtureVendorDir() . '/notes.txt', 'not a template');

    $response = $this->json('POST', '/api/get-template-file-contents', [
        'filepath' => fixtureVendorDir() . '/notes.txt',
    ]);

    $response->assertStatus(422);

    removeFixtureAndBait();
});

test('it answers a malformed template without a server error', function () {
    plantFixtureAndBait();
    File::put(fixtureVendorDir() . '/broken.yml', "main:\n\tname: " . LEAK_MARKER . "\n");
    config(['app.debug' => true]);

    $response = $this->json('POST', '/api/get-template-file-contents', [
        'filepath' => fixtureVendorDir() . '/broken.yml',
    ]);

    $response->assertStatus(422);
    $this->assertStringNotContainsString(LEAK_MARKER, $response->getContent());

    removeFixtureAndBait();
});
