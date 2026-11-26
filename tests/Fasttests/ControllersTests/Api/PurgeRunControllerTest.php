<?php

use App\Models\PurgeRun;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    PurgeRun::query()->delete();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('guests cannot read or delete purge runs', function () {
    auth()->logout();
    $run = PurgeRun::factory()->create();

    $this->getJson('/api/settings/purge-run-history')->assertUnauthorized();
    $this->deleteJson("/api/settings/purge-run-history/{$run->id}")->assertUnauthorized();
    $this->postJson('/api/settings/purge-run-history/delete-many', ['ids' => [$run->id]])->assertUnauthorized();

    expect(PurgeRun::count())->toBe(1);
});

test('lists purge runs newest first', function () {
    $first = PurgeRun::factory()->create();
    $second = PurgeRun::factory()->create();

    $response = $this->getJson('/api/settings/purge-run-history')->assertOk();

    $response->assertJsonPath('total', 2)
        ->assertJsonStructure(['data' => [['id', 'type', 'mode', 'status', 'dry_run', 'source', 'message', 'scope', 'summary', 'started_at', 'finished_at']]]);

    expect(collect($response->json('data'))->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('filters by status, run type and scope', function () {
    $failed = PurgeRun::factory()->failed()->create();
    $dryRun = PurgeRun::factory()->dryRun()->create();
    $scoped = PurgeRun::factory()->create(['scope' => ['table' => 'tracked_jobs']]);

    $ids = fn (string $query): array => collect($this->getJson("/api/settings/purge-run-history?{$query}")->assertOk()->json('data'))->pluck('id')->all();

    expect($ids('filter[status]=failed'))->toBe([$failed->id])
        ->and($ids('filter[dry_run]=1'))->toBe([$dryRun->id])
        ->and($ids('filter[dry_run]=0'))->toBe([$scoped->id, $failed->id])
        ->and($ids('filter[q]=tracked_jobs'))->toBe([$scoped->id]);
});

test('sorts and paginates', function () {
    PurgeRun::factory()->count(3)->create();

    $response = $this->getJson('/api/settings/purge-run-history?sort=id&perPage=2')->assertOk();

    expect($response->json('data.0.id'))->toBe(PurgeRun::min('id'))
        ->and($response->json('per_page'))->toBe(2)
        ->and($response->json('last_page'))->toBe(2);
});

test('load more mode caps the all rows page size', function () {
    PurgeRun::factory()->count(2)->create();

    $this->getJson('/api/settings/purge-run-history?loadMore=true&perPage=10000000')->assertOk()
        ->assertJsonPath('per_page', 50);
});

test('shows one purge run', function () {
    $run = PurgeRun::factory()->create(['summary' => ['metrics' => ['total_checked' => 6]]]);

    $this->getJson("/api/settings/purge-run-history/{$run->id}")->assertOk()
        ->assertJsonPath('summary.metrics.total_checked', 6);

    $this->getJson('/api/settings/purge-run-history/99999999')->assertNotFound();
});

test('deletes one purge run', function () {
    $run = PurgeRun::factory()->create();
    $kept = PurgeRun::factory()->create();

    $this->deleteJson("/api/settings/purge-run-history/{$run->id}")->assertOk()->assertJson(['success' => true]);

    expect(PurgeRun::pluck('id')->all())->toBe([$kept->id]);

    $this->deleteJson('/api/settings/purge-run-history/99999999')->assertNotFound();
});

test('deletes many purge runs', function () {
    $runs = PurgeRun::factory()->count(3)->create();

    $this->postJson('/api/settings/purge-run-history/delete-many', ['ids' => [$runs[0]->id, $runs[1]->id]])->assertOk();

    expect(PurgeRun::pluck('id')->all())->toBe([$runs[2]->id]);
});

test('delete many needs a list of ids', function (mixed $ids) {
    PurgeRun::factory()->create();

    $this->postJson('/api/settings/purge-run-history/delete-many', ['ids' => $ids])->assertUnprocessable();

    expect(PurgeRun::count())->toBe(1);
})->with([
    'missing' => [null],
    'empty' => [[]],
    'not a list' => ['all'],
    'not integers' => [['abc']],
]);
