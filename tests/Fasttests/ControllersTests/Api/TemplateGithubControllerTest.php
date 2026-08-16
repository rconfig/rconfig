<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TemplateGithubControllerTest extends TestCase
{
    protected $user;
    protected $templatesDstDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->templatesDstDir = templates_path() . 'rConfig-templates';
    }

    // 1. download rConfig-templates repo during deployment

    public function test_github_connectivity_test()
    {
        $response = $this->json('GET', '/api/test-template-repo-connection');

        $response->assertStatus(200)->assertJsonFragment([
            // The templates repo's default branch is now main, not master.
            'html_url' => 'https://github.com/rconfig/rConfig-templates/blob/main/.gitignore',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            'msg' => 'Successfully connected to rConfig Templates Github repo',
        ]);
    }

    public function test_failed_github_connectivity_test()
    {
        Config::set('github.git.rconfig-template-repo', '123');
        $this->assertEquals('123', Config::get('github.git.rconfig-template-repo'));

        $response = $this->json('GET', '/api/test-template-repo-connection');
        $this->assertStringContainsString('Exception thrown: Could not connect to repo - HTTP request returned status code 404', $response->json()['message']['msg']);
    }

    public function test_storage_has_github_clone()
    {
        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }

        Artisan::call('rconfig:clone-templates');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertGreaterThan(0, count($arr));

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
    }

    public function test_storage_does_not_have_github_clone()
    {
        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }
        $response = $this->json('GET', '/api/list-template-repo-folders');

        $response->assertStatus(422)->assertJsonFragment([
            'msg' => 'rConfig-templates is empty, or does not exist. Clone from "https://github.com/rconfig/rconfig-templates" may have failed! Try importing the templates again.!',
        ]);
    }

    public function test_can_get_list_of_dirs()
    {
        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }

        Artisan::call('rconfig:clone-templates');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertGreaterThan(0, count($arr));

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
    }

    public function test_given_dir_can_get_list_of_files()
    {
        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }

        Artisan::call('rconfig:clone-templates');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertGreaterThan(0, count($arr));

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
    }

    public function test_given_file_can_be_read()
    {
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
    }

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

    private const LEAK_MARKER = 'LEAKED_TEMPLATE_NAME';

    private function fixtureVendorDir(): string
    {
        return $this->templatesDstDir . '/ContainmentTestVendor';
    }

    private function baitFile(): string
    {
        return export_path() . 'containment_probe.yml';
    }

    private function baitDir(): string
    {
        return export_path() . 'containment_probe_dir';
    }

    private function plantFixtureAndBait(): void
    {
        $template = "main:\n  name: " . self::LEAK_MARKER . "\n  desc: planted template\n";

        File::ensureDirectoryExists($this->fixtureVendorDir());
        File::put($this->fixtureVendorDir() . '/valid.yml', "main:\n  name: Contained Template\n  desc: inside the repo\n");

        File::ensureDirectoryExists($this->baitDir());
        File::put($this->baitFile(), $template);
        File::put($this->baitDir() . '/probe.yml', $template);
    }

    private function removeFixtureAndBait(): void
    {
        File::delete($this->baitFile());
        File::deleteDirectory($this->baitDir());

        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }
    }

    public function test_it_lists_yml_files_inside_the_cloned_repo(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => $this->fixtureVendorDir()]);

        $response->assertStatus(200);
        $this->assertStringContainsString('valid.yml', $response->getContent());

        $this->removeFixtureAndBait();
    }

    public function test_it_refuses_to_list_a_directory_outside_the_cloned_repo(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => $this->baitDir()]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('probe.yml', $response->getContent());

        $this->removeFixtureAndBait();
    }

    public function test_it_refuses_to_list_a_directory_reached_by_traversal(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/list-repo-folders-contents', [
            'directory' => $this->fixtureVendorDir() . '/../../../exports/containment_probe_dir',
        ]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('probe.yml', $response->getContent());

        $this->removeFixtureAndBait();
    }

    /**
     * With no directory supplied the unhardened code globbed the filesystem root.
     *
     * A guard, not a regression proof: on a host with no yml file directly in / the old code
     * also returned a failure, so this case passes either way. Verified as such. It is kept
     * because the endpoint should reject a missing parameter on its own terms.
     */
    public function test_it_refuses_a_missing_directory_parameter(): void
    {
        $response = $this->json('POST', '/api/list-repo-folders-contents', []);

        $response->assertStatus(422);
        $this->assertArrayNotHasKey('data', $response->json());
    }

    public function test_it_reads_a_template_inside_the_cloned_repo(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/get-template-file-contents', [
            'filepath' => $this->fixtureVendorDir() . '/valid.yml',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['templateName' => 'Contained Template']);

        $this->removeFixtureAndBait();
    }

    public function test_it_refuses_to_read_a_template_outside_the_cloned_repo(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/get-template-file-contents', ['filepath' => $this->baitFile()]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString(self::LEAK_MARKER, $response->getContent());

        $this->removeFixtureAndBait();
    }

    public function test_it_refuses_to_read_a_template_reached_by_traversal(): void
    {
        $this->plantFixtureAndBait();

        $response = $this->json('POST', '/api/get-template-file-contents', [
            'filepath' => $this->fixtureVendorDir() . '/../../../exports/containment_probe.yml',
        ]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString(self::LEAK_MARKER, $response->getContent());

        $this->removeFixtureAndBait();
    }

    public function test_it_refuses_a_non_yaml_file_inside_the_cloned_repo(): void
    {
        $this->plantFixtureAndBait();
        File::put($this->fixtureVendorDir() . '/notes.txt', 'not a template');

        $response = $this->json('POST', '/api/get-template-file-contents', [
            'filepath' => $this->fixtureVendorDir() . '/notes.txt',
        ]);

        $response->assertStatus(422);

        $this->removeFixtureAndBait();
    }

    /**
     * A malformed template used to raise a 500, and the parser exception embeds a snippet of
     * the file it failed on, which reaches the response body when app.debug is on.
     */
    public function test_it_answers_a_malformed_template_without_a_server_error(): void
    {
        $this->plantFixtureAndBait();
        File::put($this->fixtureVendorDir() . '/broken.yml', "main:\n\tname: " . self::LEAK_MARKER . "\n");
        config(['app.debug' => true]);

        $response = $this->json('POST', '/api/get-template-file-contents', [
            'filepath' => $this->fixtureVendorDir() . '/broken.yml',
        ]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString(self::LEAK_MARKER, $response->getContent());

        $this->removeFixtureAndBait();
    }
}
