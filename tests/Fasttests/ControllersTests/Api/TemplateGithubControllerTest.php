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
        $this->actingAs($this->user, 'api');
        $this->templatesDstDir = templates_path() . 'rConfig-templates';
    }

    // 1. download rConfig-templates repo during deployment

    public function test_github_connectivity_test()
    {
        $response = $this->json('GET', '/api/test-template-repo-connection');

        $response->assertStatus(200)->assertJsonFragment([
            'html_url' => 'https://github.com/rconfig/rConfig-templates/blob/master/.gitignore',
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
            'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/Brocade',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/Checkpoint',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            'path' => rconfig_appdir_path() . '/storage/app/rconfig/templates/rConfig-templates/Sonicwall',
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
            'path' => '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Brocade',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            'path' => '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Checkpoint',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            'path' => '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Sonicwall',
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

        $response = $this->json('POST', '/api/list-repo-folders-contents', ['directory' => '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco']);

        $response->assertStatus(200)->assertJsonFragment([
            '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco/asa-ssh-enable.yml',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco/ciscowlc-ssh-noenable.yml',
        ]);
        $response->assertStatus(200)->assertJsonFragment([
            '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco/ios-ssh-noenable.yml',
        ]);

        // has readme.md
        $response->assertStatus(200)->assertJsonFragment([
            '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco/README.md',
        ]);

        if (is_dir($this->templatesDstDir)) {
            File::deleteDirectory($this->templatesDstDir);
        }
    }

    public function test_given_file_can_be_read()
    {
        Artisan::call('rconfig:clone-templates');

        $response = $this->json('POST', '/api/get-template-file-contents', ['filepath' => '/var/www/html/rconfig/storage/app/rconfig/templates/rConfig-templates/Cisco/asa-ssh-enable.yml']);

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
}
