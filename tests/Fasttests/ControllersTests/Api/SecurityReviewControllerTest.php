<?php

use App\Jobs\RunSecurityChecksJob;
use App\Models\SecurityCheckRun;
use App\Models\User;
use App\Services\Security\SecurityReviewSettings;
use App\Services\Security\SecurityTaskStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->beginTransaction();
    Queue::fake();
    app(SecurityTaskStatus::class)->releaseRun();
    SecurityCheckRun::query()->delete();
    $this->user = User::factory()->create();
});

afterEach(function () {
    app(SecurityTaskStatus::class)->releaseRun();
    $this->rollBackTransaction();
});

test('guests get 401 from every Security Review endpoint', function () {
    $this->getJson('/api/settings/security-review')->assertUnauthorized();
    $this->postJson('/api/settings/security-review/run')->assertUnauthorized();
    $this->getJson('/api/settings/security-review/tasks/' . Str::uuid())->assertUnauthorized();
    $this->putJson('/api/settings/security-review/notify', ['enabled' => false])->assertUnauthorized();

    Queue::assertNothingPushed();
});

test('index returns an empty report before any run, with the schedule and alert switch', function () {
    $this->actingAs($this->user)->getJson('/api/settings/security-review')
        ->assertOk()
        ->assertJson([
            'report' => null,
            'run' => null,
            'schedule' => ['enabled' => true, 'time' => '04:15'],
            'notify' => true,
            'advisories_url' => 'https://www.rconfig.com/security-advisories',
        ]);
});

test('index returns the latest stored run', function () {
    SecurityCheckRun::factory()->create();

    $this->actingAs($this->user)->getJson('/api/settings/security-review')
        ->assertOk()
        ->assertJsonPath('report.checks.0.id', 'app.debug')
        ->assertJsonPath('run.summary.pass', 1);
});

test('run queues one job and a second click returns the running task', function () {
    $first = $this->actingAs($this->user)->postJson('/api/settings/security-review/run')->assertStatus(202)->assertJsonPath('status', 'queued');
    $second = $this->actingAs($this->user)->postJson('/api/settings/security-review/run')->assertStatus(202)->assertJsonPath('status', 'running');

    expect($second->json('token'))->toBe($first->json('token'));
    Queue::assertPushed(RunSecurityChecksJob::class, 1);
    Queue::assertPushed(RunSecurityChecksJob::class, fn (RunSecurityChecksJob $job): bool => $job->queue === 'rConfigDefault' && $job->userId === $this->user->id);

    $this->actingAs($this->user)->getJson('/api/settings/security-review')->assertJsonPath('running_task', $first->json('token'));
});

test('the run limiter is its own counter, not exhausted by other API requests', function () {
    $this->actingAs($this->user);

    for ($i = 0; $i < 8; $i++) {
        $this->getJson('/api/settings/security-review')->assertOk();
    }

    $this->postJson('/api/settings/security-review/run')->assertStatus(202);
});

test('the run limiter allows six runs a minute per user', function () {
    $this->actingAs($this->user);

    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/settings/security-review/run')->assertStatus(202);
    }

    $this->postJson('/api/settings/security-review/run')->assertStatus(429);
});

test('task polling returns the task and 404 for unknown or malformed tokens', function () {
    $token = $this->actingAs($this->user)->postJson('/api/settings/security-review/run')->json('token');

    $this->getJson("/api/settings/security-review/tasks/{$token}")->assertOk()->assertJson(['token' => $token, 'status' => 'queued']);

    app(SecurityTaskStatus::class)->update($token, ['status' => 'done', 'result' => ['summary' => ['fail' => 0]]]);
    $this->getJson("/api/settings/security-review/tasks/{$token}")->assertJsonPath('status', 'done');

    $this->getJson('/api/settings/security-review/tasks/' . Str::uuid())->assertNotFound();
    $this->getJson('/api/settings/security-review/tasks/not-a-uuid')->assertNotFound();
});

test('the alert switch is validated and persisted in settings', function () {
    $this->actingAs($this->user);

    $this->putJson('/api/settings/security-review/notify', ['enabled' => 'maybe'])->assertUnprocessable()->assertJsonValidationErrors('enabled');

    $this->putJson('/api/settings/security-review/notify', ['enabled' => false])->assertOk()->assertJson(['notify' => false]);
    expect(DB::table('settings')->where('id', 1)->value('security_check_notify'))->toBe(0)
        ->and(app(SecurityReviewSettings::class)->notifyEnabled())->toBeFalse();
    $this->getJson('/api/settings/security-review')->assertJsonPath('notify', false);

    $this->putJson('/api/settings/security-review/notify', ['enabled' => true])->assertOk()->assertJson(['notify' => true]);
});

test('the alert switch falls back to the config default until it is saved', function () {
    DB::table('settings')->where('id', 1)->update(['security_check_notify' => null]);

    config(['security-checks.notify' => false]);
    expect(app(SecurityReviewSettings::class)->notifyEnabled())->toBeFalse();

    config(['security-checks.notify' => true]);
    expect(app(SecurityReviewSettings::class)->notifyEnabled())->toBeTrue();
});
