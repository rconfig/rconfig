<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\Tag;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_tag_requires_a_name()
    {
        $response = $this->json('post', '/api/tags', ['tagname' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('tagname', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_tag()
    {
        $tag = \App\Models\Tag::factory()->create();
        $response = $this->get('/api/tags/' . $tag->id);

        $response->assertJson(['tagname' => $tag->tagname]);
    }

    public function test_get_all_tags()
    {
        $tag = \App\Models\Tag::factory(100)->create();
        $response = $this->get('/api/tags?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_tag()
    {
        $tag = \App\Models\Tag::factory()->create();
        $this->post('/api/tag', $tag->toArray());

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tagname' => $tag->tagname,
        ]);
    }

    public function test_edit_tag()
    {
        $tag = \App\Models\Tag::factory()->create();

        $response = $this->patch('/api/tags/' . $tag->id, [
            'tagname' => 'a-new-tag-name',
            'tagDescription' => 'this is a new tag description',
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tagname' => 'a-new-tag-name',
            'tagDescription' => 'this is a new tag description',
        ]);
    }

    public function test_delete_tag()
    {
        $tag = \App\Models\Tag::factory()->create();

        $this->delete('/api/tags/' . $tag->id);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_count_devices_with_tag()
    {
        //set config('queue') to redis to bypass DownloadConfigNow.php in device store and avoid errors due to incomplete data for the test
        config(['queue.default' => 'redis']);

        $tags = Tag::factory(5)->create();

        foreach ($tags as $tag) {
            $device = Device::factory()->make([
                'device_category_id' => 1011,
                'device_vendor' => 1,
                'device_tags' => $tags,
                'device_template' => 1,
            ]);
            $this->assertDatabaseMissing('devices', [
                'id' => $device->id,
            ]);

            $response = $this->json('post', '/api/devices', $device->toArray());
            $result = json_decode($response->getContent());

            $this->assertDatabaseHas('devices', [
                'id' => $result->data->id,
            ]);

            $this->assertDatabaseHas('device_tag', [
                'device_id' => $result->data->id,
                'tag_id' => $device->device_tags[0]->id,
            ]);
        }

        $response = $this->get('/api/tags/' . $tags[4]->id);
        $response->assertJsonFragment(['device_count' => 5]);

        config(['queue.default' => 'sync']);
    }
}
