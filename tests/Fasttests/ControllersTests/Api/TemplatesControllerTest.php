<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Template;
use Tests\TestCase;

class TemplatesControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_template_requires_a_name()
    {
        $response = $this->json('post', '/api/templates', ['fileName' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('fileName', $response['errors']);
        $this->assertArrayHasKey('code', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_template()
    {
        $response = $this->get('/api/templates/'. 1);
        $response->assertJson([
            'fileName' => 'ios-telnet-noenable.yml',
            'templateName' => 'Cisco IOS - TELNET - No Enable',
        ]);

        $response->assertSee('# rConfig connection template -'); // test file data has returned
    }

    public function test_can_get_default_template()
    {
        $template = \App\Models\Template::factory()->create();
        $response = $this->get('/api/get-default-template');

        $this->assertStringContainsString('name: "Name of Template"', $response->getContent());
    }

    public function test_get_all_templates()
    {
        $template = \App\Models\Template::factory(10)->create();
        $response = $this->get('/api/templates?page=1&perPage=100');
        $this->assertGreaterThan(10, $response->json()['data']);
        $response->assertStatus(200);
    }

    public function test_create_template()
    {
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));

        $response = $this->post('/api/templates', [
            'fileName' => 'test file name',
            'code' => $code,
        ]);

        $json = json_decode($response->getContent());

        $response->assertStatus(200);
        $this->assertDatabaseHas('templates', [
            'fileName' => '/app/rconfig/templates/test_file_name.yml',
        ]);
        $this->assertFileExists(storage_path().'/app/rconfig/templates/test_file_name.yml');
        unlink(storage_path().'/app/rconfig/templates/test_file_name.yml');
    }

    public function test_edit_template()
    {
        // Add first
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
        $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
            'fileName' => 'test file name',
            'code' => $code,
        ]);

        $latestTemplate = Template::orderBy('id', 'desc')->first();
        $response->assertStatus(200);
        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/test_file_name.yml',
        ]);

        // Then Edit
        $code2 = file_get_contents(base_path('tests/storage/default_template_test2.yml'));
        $response2 = $this->patch('/api/templates/'.$latestTemplate->id, [
            'fileName' => 'a-new-file-name2',
            'code' => $code2,
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/a-new-file-name2.yml',
        ]);

        // Then get the new template and read the code
        $response3 = $this->get('/api/templates/'.$latestTemplate->id);
        $response3->assertSee('This is a test Template Number2 for edit tests'); // test file data has returned
        unlink(storage_path().'/app/rconfig/templates/test_file_name.yml');
    }

    public function test_delete_template()
    {
        // Add first
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
        $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
            'fileName' => 'test file name',
            'code' => $code,
        ]);

        $json = json_decode($response->getContent());
        $latestTemplate = Template::orderBy('id', 'desc')->first();

        $response->assertStatus(200);
        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/test_file_name.yml',
        ]);

        // Then Delete
        $this->delete('/api/templates/'.$latestTemplate->id);

        $this->assertFileDoesNotExist(storage_path().'/app/rconfig/templates/test_file_name.yml');
        $this->assertDatabaseMissing('templates', ['id' => $latestTemplate->id]);
    }
}
