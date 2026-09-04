<?php

use App\Http\Controllers\Api\TemplateController;
use App\Models\Device;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('a template requires a name', function () {
    $response = $this->json('post', '/api/templates', ['templateName' => null]);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('templateName');
    expect($response['errors'])->toHaveKey('code');
    $response->assertStatus(422);
});

test('show single template', function () {
    $response = $this->get('/api/templates/' . 1);
    $response->assertStatus(200);

    $response->assertJson([
        'fileName' => 'ios-telnet-noenable.yml',
        'templateName' => 'Cisco IOS - TELNET - No Enable',
    ]);

    $response->assertSee('# rConfig connection template -');
    // test file data has returned
});

test('can get default template', function () {
    $template = Template::factory()->create();
    $response = $this->get('/api/get-default-template');

    $this->assertStringContainsString('name: "Name of Template"', $response->getContent());
});

test('show nonexistent template returns 404', function () {
    $response = $this->get('/api/templates/99999');
    $response->assertStatus(404);
});

test('show template with missing file reports it rather than failing', function () {
    $template = Template::factory()->create([
        'fileName' => '/app/rconfig/templates/definitely_not_on_disk.yml',
    ]);

    $response = $this->get('/api/templates/' . $template->id);

    $response->assertStatus(200);
    $response->assertJson([
        'fileMissing' => true,
        'code' => '',
        'fileName' => 'definitely_not_on_disk.yml',
    ]);
});

test('missing template file response does not disclose the absolute path', function () {
    $template = Template::factory()->create([
        'fileName' => '/app/rconfig/templates/definitely_not_on_disk.yml',
    ]);

    $response = $this->get('/api/templates/' . $template->id);

    $response->assertDontSee(storage_path(), false);
    $response->assertDontSee('/app/rconfig/templates/definitely_not_on_disk.yml', false);
});

test('show template present on disk is not flagged as missing', function () {
    $response = $this->get('/api/templates/' . 1);

    $response->assertStatus(200);
    $response->assertJson(['fileMissing' => false]);
});

test('get all templates', function () {
    $template = Template::factory(10)->create();
    $response = $this->get('/api/templates?page=1&perPage=100');
    expect($response->json()['data'])->toBeGreaterThan(10);
    $response->assertStatus(200);
});

test('edit template', function () {
    // Ensure the templates directory exists
    $templatesDir = storage_path('app/rconfig/templates');
    if (! is_dir($templatesDir)) {
        mkdir($templatesDir, 0755, true);
    }

    // delete the test file if it exists
    $testFile = storage_path('app/rconfig/templates/test_file_name.yml');
    if (file_exists($testFile)) {
        unlink($testFile);
    }

    // Add first
    $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
    $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
        'templateName' => 'test file name',
        'code' => $code,
        'description' => 'Test template description',
    ]);

    $latestTemplate = Template::orderBy('id', 'desc')->first();
    $response->assertStatus(200);
    $this->assertDatabaseHas('templates', [
        'id' => $latestTemplate->id,
        'fileName' => '/app/rconfig/templates/test_file_name.yml',
    ]);

    // Then Edit - include the current fileName so the controller can find the old file
    $code2 = file_get_contents(base_path('tests/storage/default_template_test2.yml'));
    $response2 = $this->withHeaders(['Accept' => 'application/json'])->patch('/api/templates/' . $latestTemplate->id, [
        'templateName' => 'a-new-file-name2',
        'code' => $code2,
        'description' => 'Updated test template description',
        'fileName' => 'test_file_name.yml', // Add the current fileName so controller can delete old file
    ]);
    $response2->assertStatus(200);

    $this->assertDatabaseHas('templates', [
        'id' => $latestTemplate->id,
        'fileName' => '/app/rconfig/templates/a_new_file_name2.yml',
    ]);

    // Then get the new template and read the code
    $response3 = $this->get('/api/templates/' . $latestTemplate->id);
    $response3->assertSee('This is a test Template Number2 for edit tests');

    // Clean up both possible file names
    $oldFile = storage_path('app/rconfig/templates/test_file_name.yml');
    $newFile = storage_path('app/rconfig/templates/a_new_file_name2.yml');

    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
    if (file_exists($newFile)) {
        unlink($newFile);
    }
});

test('delete template', function () {
    // Add first
    $testFile = storage_path('app/rconfig/templates/test_file_name.yml');
    if (file_exists($testFile)) {
        unlink($testFile);
    }

    $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));
    $response = $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
        'templateName' => 'test file name',
        'description' => 'Test template description',
        'code' => $code,
    ]);

    $response->assertStatus(200);

    $json = json_decode($response->getContent());
    $latestTemplate = Template::orderBy('id', 'desc')->first();

    $this->assertDatabaseHas('templates', [
        'id' => $latestTemplate->id,
        'fileName' => '/app/rconfig/templates/test_file_name.yml',
    ]);

    // Then Delete
    $this->delete('/api/templates/' . $latestTemplate->id);

    $this->assertFileDoesNotExist(storage_path() . '/app/rconfig/templates/test_file_name.yml');
    $this->assertDatabaseMissing('templates', ['id' => $latestTemplate->id]);
});

test('delete many removes physical file and allows recreation', function () {
    $testFile = storage_path('app/rconfig/templates/test_file_name.yml');
    if (file_exists($testFile)) {
        unlink($testFile);
    }

    $code = file_get_contents(base_path('tests/storage/default_template_test.yml'));

    // Create a template (writes the physical file).
    $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
        'templateName' => 'test file name',
        'description' => 'Test template description',
        'code' => $code,
    ])->assertStatus(200);

    $template = Template::orderBy('id', 'desc')->first();
    expect($testFile)->toBeFile();

    // Bulk delete should remove the record and the physical file.
    $this->post('/api/templates/delete-many', ['ids' => [$template->id]])->assertStatus(200);

    $this->assertDatabaseMissing('templates', ['id' => $template->id]);
    $this->assertFileDoesNotExist($testFile);

    // The same name can now be recreated without a file collision.
    $this->withHeaders(['Accept' => 'application/json'])->json('POST', '/api/templates', [
        'templateName' => 'test file name',
        'description' => 'Recreated after bulk delete',
        'code' => $code,
    ])->assertStatus(200);

    $this->assertDatabaseHas('templates', [
        'fileName' => '/app/rconfig/templates/test_file_name.yml',
    ]);

    if (file_exists($testFile)) {
        unlink($testFile);
    }
});

test('cannot delete category with existing device relationships', function () {
    $this->expectException(Exception::class);
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
});

test('delete many returns error if any template has device relationship', function () {
    $this->expectException(Exception::class);
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
});

test('get template device relationship but not disabled devices', function () {
    $device = Device::factory()->create(['status' => 100]);
    $template = Template::factory()->create();
    $template->device()->attach($device->id);

    $cat = Template::with('device')->where('id', $template->id)->get();

    expect($cat)->toHaveCount(1);
    expect($cat[0]->device)->toHaveCount(0);
});

test('sanitize file name', function () {
    $controller = new TemplateController(new Template);

    // Test with spaces - should be converted to underscores
    $result = $controller->sanitizeFileName('test file name');
    expect($result)->toEqual('test_file_name.yml');

    // Test with special characters
    $result = $controller->sanitizeFileName('test@file#name$');
    $this->assertStringNotContainsString('@', $result);
    $this->assertStringNotContainsString('#', $result);
    $this->assertStringNotContainsString('$', $result);
    expect($result)->toEndWith('.yml');

    // Test with already clean filename
    $result = $controller->sanitizeFileName('clean_filename');
    expect($result)->toEqual('clean_filename.yml');

    // Test with empty string
    $result = $controller->sanitizeFileName('');
    expect($result)->not->toBeEmpty();
    expect($result)->toEndWith('.yml');

    // Test with numbers and letters
    $result = $controller->sanitizeFileName('test123file');
    expect($result)->toEqual('test123file.yml');

    // Test with dots and dashes
    $result = $controller->sanitizeFileName('test.file-name');
    expect($result)->toEndWith('.yml');
    $this->assertStringContainsString('test', $result);
    $this->assertStringContainsString('file', $result);
    $this->assertStringContainsString('name', $result);
});

afterEach(function () {
    $this->rollBackTransaction();
});
