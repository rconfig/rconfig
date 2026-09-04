<?php

use App\Models\Device;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all tags with tagname filter', function () {
    $tags = Tag::factory()->create(['tagname' => 'TagsControllerTestTagDesc']);

    $response = $this->get('/api/tags?page=1&perPage=100&filter[q]=TagsControllerTestTagDesc');

    expect(count($response['data']))->toEqual(1);
    $response->assertStatus(200);
    $response->assertJsonFragment(['tagname' => 'TagsControllerTestTagDesc']);

    $tags->delete();
});

test('a tag requires a name', function () {
    $response = $this->json('post', '/api/tags', ['tagname' => null]);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('tagname');
    $response->assertStatus(422);
});

test('show single tag', function () {
    $tag = Tag::factory()->create(['tagDescription' => 'TagsControllerTestTagDesc']);
    $response = $this->get('/api/tags/' . $tag->id);

    $response->assertJson(['tagname' => $tag->tagname]);
});

test('get all tags', function () {
    $tags = Tag::factory(100)->create(['tagDescription' => 'TagsControllerTestTagDesc']);

    $response = $this->get('/api/tags?page=1&perPage=100');
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('create tag', function () {
    $tag = Tag::factory()->make(['tagDescription' => 'TagsControllerTestTagDesc']);
    $this->post('/api/tags', $tag->toArray());

    $this->assertDatabaseHas('tags', [
        'tagname' => $tag->tagname,
    ]);
});

test('edit tag', function () {
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
});

test('delete tag', function () {
    $tag = Tag::factory()->create(['tagDescription' => 'TagsControllerTestTagDesc']);

    $this->delete('/api/tags/' . $tag->id);

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

test('cannot delete tags with existing device relationships', function () {
    $this->expectException(Exception::class);
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
});

test('cannot delete tags with existing device relationships if more than one', function () {
    $this->expectException(Exception::class);
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
});

test('get tag device relationship but not disabled devices', function () {
    $seededTags = Tag::factory(3)->create()->sortBy('id')->values();
    $device = Device::factory()->create(['status' => 1]);

    DB::table('device_tag')->insert([
        'device_id' => $device->id,
        'tag_id' => $seededTags->last()->id,
    ]);

    $tags = Tag::with('device')->whereIn('id', $seededTags->pluck('id'))->orderBy('id', 'asc')->get();

    expect($tags)->toHaveCount(3);
    expect(count($tags[2]->device))->toBeGreaterThan(0);
});

afterEach(function () {
    $this->rollBackTransaction();
});
