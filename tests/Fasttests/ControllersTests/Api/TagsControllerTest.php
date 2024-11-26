<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Device;
use App\Models\Tag;
use App\Models\User;
use Psy\VarDumper\Dumper;
use Tests\TestCase;

class TagsControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
        $tag = Tag::factory()->create();
        $response = $this->get('/api/tags/' . $tag->id);

        $response->assertJson(['tagname' => $tag->tagname]);
    }

    public function test_get_all_tags_with_generic_filter()
    {
        $response = $this->get('/api/tags?page=1&perPage=100&filter[tagname]=switch');
        $response->assertJsonFragment(['tagname' => 'Switches']);
        $response->assertJsonFragment(['total' => 1]);
        $response->assertStatus(200);
    }

    public function test_get_all_tags()
    {
        $tag = Tag::factory(100)->create();
        $response = $this->get('/api/tags?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_tag()
    {
        $tag = Tag::factory()->make();
        $response = $this->post('/api/tags', $tag->toArray());
        $response->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'tagname' => $tag->tagname,
        ]);
    }

    public function test_edit_tag()
    {
        $tag = Tag::factory()->create();

        $response = $this->patch('/api/tags/' . $tag->id, [
            'tagname' => 'a-new-tag-name',
            'tagDescription' => 'this is a new tag description',
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'tagname' => 'a-new-tag-name',
            'tagDescription' => 'this is a new tag description',
        ]);
    }

    public function test_delete_tag()
    {
        $tag = Tag::factory()->create();

        $this->delete('/api/tags/' . $tag->id);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_cannot_delete_tags_with_existing_device_relationships()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete tag set with related devices.');

        $tag = Tag::factory()->create();
        $device = Device::factory()->create();
        $device->tag()->attach($tag->id);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        $response = $this->delete('/api/tags/' . $tag->id);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete tag set with related devices.']);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);

        // delete the command and the category and the relationship
        $tag->delete();
        $device->delete();
        $device->category()->detach($tag->id);
    }

    public function test_deleteMany_returns_error_if_any_category_has_device_relationship()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete tag set with related devices.');

        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $device = Device::factory()->create();
        $device->tag()->attach($tag->id);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        $this->assertDatabaseHas('devices', ['id' => $device->id]);

        $response = $this->post('/api/tags/delete-many', ['ids' => [$tag->id, $tag2->id]]);
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot delete tag set with related devices.']);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        $this->assertDatabaseHas('tags', ['id' => $tag2->id]);

        // delete the command and the category and the relationship
        $tag->delete();
        $device->delete();
        $device->category()->detach($tag->id);
    }

    public function test_get_tag_device_relationship_but_not_disabled_devices()
    {
        $device = Device::factory()->create(['status' => 100]);
        $tag = Tag::factory()->create();
        $tag->device()->attach($device->id);

        $cat = Tag::with('device')->where('id', $tag->id)->get();

        $this->assertCount(1, $cat);
        $this->assertCount(0, $cat[0]->device);
    }
}
