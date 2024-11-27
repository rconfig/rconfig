<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\Template;
use App\Models\User;
use Tests\TestCase;

class TemplatesControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_template_requires_a_name()
    {
        $response = $this->json('post', '/api/templates', ['templateName' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('templateName', $response['errors']);
        $this->assertArrayHasKey('code', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_template()
    {
        $response = $this->get('/api/templates/' . 1);
        $response->assertStatus(200);

        $response->assertJson([
            'fileName' => 'ios-telnet-noenable.yml',
            'templateName' => 'Cisco IOS - TELNET - No Enable',
        ]);

        $response->assertSee('# rConfig connection template -'); // test file data has returned
    }

    public function test_can_get_default_template()
    {
        $template = Template::factory()->create();
        $response = $this->get('/api/get-default-template');

        $this->assertStringContainsString('name: "Name of Template"', $response->getContent());
    }

    public function test_get_all_templates()
    {
        $template = Template::factory(10)->create();
        $response = $this->get('/api/templates?page=1&perPage=100');
        $this->assertGreaterThan(10, $response->json()['data']);
        $response->assertStatus(200);
    }

    public function test_get_all_templates_with_generic_filter()
    {
        $response = $this->get('/api/templates?page=1&perPage=100&filter[templateName]=SSH');
        $response->assertJsonFragment(['templateName' => 'Cisco IOS - SSH - No Enable']);
        $response->assertJsonFragment(['total' => 3]);
        $response->assertStatus(200);
    }

    public function test_create_template()
    {
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));

        $response = $this->post('/api/templates', [
            'templateName' => 'test file name',
            'code' => $code,
        ]);
        $response->assertStatus(200);
        $json = json_decode($response->getContent());

        $this->assertDatabaseHas('templates', [
            'fileName' => '/app/rconfig/templates/test-file-name.yml',
        ]);

        $this->assertFileExists(storage_path() . '/app/rconfig/templates/test-file-name.yml');
        unlink(storage_path() . '/app/rconfig/templates/test-file-name.yml');
    }

    public function test_edit_template()
    {
        // Add first
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
        $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
            'templateName' => 'test file name',
            'code' => $code,
        ]);
        $response->assertStatus(200);

        $latestTemplate = Template::orderBy('id', 'desc')->first();

        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/test-file-name.yml',
        ]);

        // Then Edit
        $code2 = file_get_contents(base_path('tests/storage/default_template_test2.yml'));
        $response2 = $this->patch('/api/templates/' . $latestTemplate->id, [
            'templateName' => 'a new file name2',
            'code' => $code2,
        ]);
        $response2->assertStatus(200);

        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/a-new-file-name2.yml',
        ]);

        // Then get the new template and read the code
        $response3 = $this->get('/api/templates/' . $latestTemplate->id);
        $response3->assertSee('This is a test Template Number2 for edit tests'); // test file data has returned
        unlink(storage_path() . '/app/rconfig/templates/test-file-name.yml');
    }

    public function test_delete_template()
    {
        // Add first
        $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
        $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
            'templateName' => 'test file name',
            'code' => $code,
        ]);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $latestTemplate = Template::orderBy('id', 'desc')->first();

        $response->assertStatus(200);
        $this->assertDatabaseHas('templates', [
            'id' => $latestTemplate->id,
            'fileName' => '/app/rconfig/templates/test-file-name.yml',
        ]);

        // Then Delete
        $this->delete('/api/templates/' . $latestTemplate->id);

        $this->assertFileDoesNotExist(storage_path() . '/app/rconfig/templates/test-file-name.yml');
        $this->assertDatabaseMissing('templates', ['id' => $latestTemplate->id]);
    }

    public function test_cannot_delete_category_with_existing_device_relationships()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete template with related devices.');

        $template = Template::factory()->create();
        $device = Device::factory()->create(['device_template' => $template->id]);
        // attached Template to a device
        $device->template()->attach($template->id);

        $this->assertDatabaseHas('templates', ['id' => $template->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_template' => $template->id]);
        $this->assertDatabaseHas('device_template', ['device_id' => $device->id, 'template_id' => $template->id]);

        $response = $this->delete('/api/templates/' . $template->id);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete template with related devices.']);

        // check again if the category and device still exist
        $this->assertDatabaseHas('templates', ['id' => $template->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_template' => $template->id]);

        // delete the command and the category and the relationship
        $template->delete();
        $device->delete();
        $device->category()->detach($template->id);
    }

    public function test_deleteMany_returns_error_if_any_template_has_device_relationship()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete template with related devices.');

        $template = Template::factory()->create();
        $template2 = Template::factory()->create();
        $device = Device::factory()->create(['device_template' => $template->id]);
        // attached template to a device
        $device->template()->attach($template->id);

        $this->assertDatabaseHas('templates', ['id' => $template->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_template' => $template->id]);
        $this->assertDatabaseHas('device_template', ['device_id' => $device->id, 'template_id' => $template->id]);

        $response = $this->post('/api/templates/delete-many', ['ids' => [$template->id, $template2->id]]);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete template with related devices.']);

        // check again if the template and device still exist
        $this->assertDatabaseHas('templates', ['id' => $template->id]);
        $this->assertDatabaseHas('templates', ['id' => $template2->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id, 'device_template' => $template->id]);

        // delete the command and the template and the relationship
        $template->delete();
        $device->delete();
        $device->template()->detach($template->id);
    }

    public function test_get_template_device_relationship_but_not_disabled_devices()
    {
        $device = Device::factory()->create(['status' => 100]);
        $template = Template::factory()->create();
        $template->device()->attach($device->id);

        $cat = Template::with('device')->where('id', $template->id)->get();

        $this->assertCount(1, $cat);
        $this->assertCount(0, $cat[0]->device);
    }
}
