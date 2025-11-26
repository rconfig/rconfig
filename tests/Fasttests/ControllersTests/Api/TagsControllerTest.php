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
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_get_all_tags_with_tagname_filter()
    {
        $tags = Tag::factory()->create(['tagname' => 'TagsControllerTestTagDesc']);

        $response = $this->get('/api/tags?page=1&perPage=100&filter[q]=TagsControllerTestTagDesc');

        $this->assertEquals(1, count($response['data']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['tagname' => 'TagsControllerTestTagDesc']);

        $tags->delete();
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
        $tag = Tag::factory()->create(['tagDescription' => 'TagsControllerTestTagDesc']);
        $response = $this->get('/api/tags/' . $tag->id);

        $response->assertJson(['tagname' => $tag->tagname]);
    }

    public function test_get_all_tags()
    {
        $tags = Tag::factory(100)->create(['tagDescription' => 'TagsControllerTestTagDesc']);

        $response = $this->get('/api/tags?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_tag()
    {
        $tag = Tag::factory()->make(['tagDescription' => 'TagsControllerTestTagDesc']);
        $this->post('/api/tags', $tag->toArray());

        $this->assertDatabaseHas('tags', [
            'tagname' => $tag->tagname,
        ]);
    }

    public function test_edit_tag()
    {
        $tag = Tag::factory()->create(['tagDescription' => 'TagsControllerTestTagDesc']);

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
        $tag = Tag::factory()->create(['tagDescription' => 'TagsControllerTestTagDesc']);

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
        $response->assertStatus(500);
        $response->assertJsonFragment(['message' => 'Cannot delete tag set with related devices.']);

        $this->assertDatabaseHas('commands', ['id' => $tag->id]);

        // delete the command and the category and the relationship
        $tag->delete();
        $device->delete();
        $device->category()->detach($tag->id);
    }

    public function test_cannot_delete_tags_with_existing_device_relationships_if_more_than_one()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete tag set with related devices.');

        $tag = Tag::factory()->create();
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();
        // attached command to a category
        $device1->tag()->attach($tag->id);
        $device2->tag()->attach($tag->id);

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        $this->assertDatabaseHas('devices', ['id' => $device1->id]);

        $response = $this->delete('/api/tags/' . $tag->id);
        $response->assertStatus(500);
        $response->assertJsonFragment(['message' => 'Cannot delete tag set with related devices.']);

        $this->assertDatabaseHas('commands', ['id' => $tag->id]);

        // delete the command and the category and the relationship
        $tag->delete();
        $device1->delete();
        $device2->delete();
        $device1->category()->detach($tag->id);
        $device2->category()->detach($tag->id);
    }

    public function test_get_tag_device_relationship_but_not_disabled_devices()
    {
        $tags = Tag::with('device')->whereIn('id', [1, 2, 1003])->orderBy('id', 'asc')->get();

        $this->assertCount(3, $tags);
        $this->assertGreaterThan(0, count($tags[2]->device));
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
