<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Jobs\DownloadConfigNow;
use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Queue;
use Tests\TestCase;

class DevicesControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_get_all_devices()
    {
        Device::factory(100)->create();
        $response = $this->get('/api/devices?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_devices_have_relationships()
    {
        Device::factory(100)->create();
        $response = $this->get('/api/devices?page=1&perPage=100');
        $response->assertJsonFragment(['category' => []]);
        $response->assertJsonFragment(['tag' => []]);
        $response->assertJsonFragment(['vendor' => []]);
        $response->assertJsonFragment(['template' => []]);
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_get_all_devices_with_generic_filter()
    {
        $response = $this->get('/api/devices?page=1&perPage=100&filter[q]=192.168.1.170');
        $response->assertJsonFragment(['device_ip' => '192.168.1.170']);
        $response->assertJsonFragment(['total' => 4]);
        $response->assertStatus(200);
    }

    public function test_get_one_device()
    {
        $device = Device::factory()->create();
        $response = $this->get('/api/devices/' . $device->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(
            [
                'id' => $device->id,
                'device_name' => $device->device_name,
                'device_password' => $device->device_password,
            ]
        );
        $response->assertJsonStructure(
            [
                'vendor',
                'category',
                'tag',
                'template',
            ]
        );
    }

    public function test_get_list_of_device_models()
    {
        $devices = Device::factory(10)->create();
        $models = $devices->pluck('device_model');
        $response = $this->get('/api/get-device-models');

        $response->assertStatus(200);
        $response->assertJsonFragment(['CSR1000v']);
        $response->assertJsonFragment([$models[0]]);
        $response->assertJsonFragment([$models[1]]);
        $response->assertJsonFragment([$models[9]]);
    }

    public function test_get_list_of_a_devices_commands()
    {
        $category = Category::factory()->create();
        $device = Device::factory()->create((['device_category_id' => $category->id]));
        $commands = Command::factory(5)->create();

        foreach ($commands as $command) {
            DB::table('category_command')->insert(
                [
                    'command_id' => $command->id,
                    'category_id' => $device->device_category_id,
                ]
            );
        }

        DB::table('category_device')->insert(
            [
                'device_id' => $device->id,
                'category_id' => $category->id,
            ]
        );

        $this->assertDatabaseHas('category_command', [
            'command_id' => $commands[0]->id,
            'category_id' => $device->device_category_id,
        ]);
        $this->assertDatabaseHas('category_device', [
            'device_id' => $device->id,
            'category_id' => $category->id,
        ]);

        $response = $this->get('/api/devices/' . $device->id);
        $response->assertJsonFragment([
            'categoryName' => $category->categoryName,
        ]);
        $this->assertCount(5, $response->json()['category'][0]['command']);
        $response->assertStatus(200);
    }

    public function test_a_device_requires_fields()
    {
        $response = $this->json('post', '/api/devices');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['device_name', 'device_vendor', 'device_model', 'device_tags', 'device_template', 'device_main_prompt']);
    }

    public function test_create_device()
    {

        Queue::fake();

        $category = Category::factory()->create();
        $vendor = Vendor::factory(1)->create();
        $commands = Command::factory(5)->create();
        $template = Template::factory(1)->create();
        $tags = Tag::factory(3)->create();

        foreach ($commands as $command) {
            DB::table('category_command')->insert(
                [
                    'command_id' => $command->id,
                    'category_id' => $category->id,
                ]
            );
        }

        $device = Device::factory()->make([
            'device_category_id' => $category->id,
            'device_vendor' => $vendor->toArray(),
            'device_tags' => $tags,
            'device_template' => $template->toArray(),
        ]);

        $this->assertDatabaseHas('category_command', [
            'command_id' => $commands[0]->id,
            'category_id' => $device->device_category_id,
        ]);

        $this->assertDatabaseMissing('devices', [
            'id' => $device->id,
        ]);

        $response = $this->json('post', '/api/devices', $device->toArray());
        $response->assertStatus(200);

        $result = json_decode($response->getContent());

        $this->assertDatabaseHas('devices', [
            'id' => $result->data->id,
        ]);

        $this->assertDatabaseHas('devices', [
            'device_username' => $device->device_username,
            'device_name' => $device->device_name,
        ]);

        $this->assertDatabaseHas('device_template', [
            'device_id' => $result->data->id,
            'template_id' => $device->device_template[0]['id'],
        ]);

        $this->assertDatabaseHas('device_tag', [
            'device_id' => $result->data->id,
            'tag_id' => $device->device_tags[0]->id,
        ]);

        $this->assertDatabaseHas('device_vendor', [
            'device_id' => $result->data->id,
            'vendor_id' => $device->device_vendor[0]['id'],
        ]);

        Queue::assertPushed(DownloadConfigNow::class);
    }

    public function test_edit_device()
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory(1)->create();
        $commands = Command::factory(5)->create();
        $template = Template::factory(1)->create();
        $tags = Tag::factory(3)->create();

        foreach ($commands as $command) {
            DB::table('category_command')->insert(
                [
                    'command_id' => $command->id,
                    'category_id' => $category->id,
                ]
            );
        }

        $device = Device::factory()->create([
            'device_category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_name' => $device->device_name,
        ]);

        $response = $this->patch('/api/devices/' . $device->id, [
            'device_name' => 'a_new_device_name',
            'device_ip' => '12.12.12.12',
            'device_username' => 'stacky',
            'device_password' => $device->device_password,
            'device_model' => $device->device_model,
            'device_vendor' => $vendor[0]['id'],
            'device_tags' => $tags->pluck('id')->toArray(),
            'device_template' => $template[0]['id'],
            'device_main_prompt' => $device->device_main_prompt,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_name' => 'a_new_device_name',
            'device_ip' => '12.12.12.12',
            'device_username' => 'stacky',
        ]);
    }

    public function test_delete_device()
    {
        $device = Device::factory()->create();
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        $this->delete('/api/devices/' . $device->id);

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);
    }

    public function test_add_serialised_encrypted_password_and_decrypt_correctly_if_pw_is_serialised_v5_migration_bug()
    {
        DB::table('devices')->insert([
            'id' => 1111111,
            'device_name' => 'test_device',
            'device_ip' => '1.1.1.1',
            'device_password' => \Crypt::encrypt('v5_encrypted_password'),
            'device_enable_password' => \Crypt::encrypt('v5_encrypted_password'),
            'device_template' => 1,
        ]);

        // dd(Device::all());
        $v5EncryptedPassword = Device::select('device_password')->where('id', 1111111)->first();
        $v6EncryptedPassword = Device::select('device_password')->where('id', 1001)->first();
        $this->assertFalse($this->is_serialized($v5EncryptedPassword->device_password));
        $this->assertFalse($this->is_serialized($v6EncryptedPassword->device_password));

        $response = $this->get('/api/devices/' . 1001);
        $response->assertStatus(200);
        $response->assertJson([
            'id' => 1001,
            'device_password' => 'cisco',
            'device_enable_password' => 'cisco',
        ]);

        $response = $this->get('/api/devices/' . 1111111);
        $response->assertStatus(200);
        $response->assertJson([
            'id' => 1111111,
            'device_password' => 'v5_encrypted_password',
            'device_enable_password' => 'v5_encrypted_password',
        ]);
        $response = $this->json('delete', '/api/devices/' . 1111111);
        $this->assertDatabaseMissing('devices', ['id' => 1111111]);
    }

    private function is_serialized($string)
    {
        try {
            unserialize($string);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
