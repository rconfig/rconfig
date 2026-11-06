<?php

use App\HealthChecks\HttpsCheck;
use App\Services\System\SelfSignedSslService;
use Spatie\Health\Enums\Status;
use Spatie\Health\Facades\Health;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

test('it is ok when APP_URL is https', function () {
    config(['app.url' => 'HTTPS://rconfig.example.com']);
    $this->fakeHostFilesystem();

    expect(app(HttpsCheck::class)->run()->status->equals(Status::ok()))->toBeTrue();
});

test('it warns over HTTP and names the command', function () {
    config(['app.url' => 'http://rconfig.example.com']);
    $this->fakeHostFilesystem();

    $result = app(HttpsCheck::class)->run();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getShortSummary())->toBe('Enable HTTPS')
        ->and($result->getNotificationMessage())->toContain(SelfSignedSslService::COMMAND);
});

test('in Docker it points at the reverse proxy instead of the command', function () {
    config(['app.url' => 'http://rconfig.example.com']);
    $this->fakeHostFilesystem(isDocker: true);

    $result = app(HttpsCheck::class)->run();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('reverse proxy')
        ->and($result->getNotificationMessage())->not->toContain(SelfSignedSslService::COMMAND);
});

test('it is registered straight after Worker Privilege', function () {
    $names = collect(Health::registeredChecks())->map(fn ($check) => $check->getName())->values()->all();
    $workerPrivilege = array_search('Worker Privilege', $names, true);

    expect($workerPrivilege)->not->toBeFalse()
        ->and($names[$workerPrivilege + 1] ?? null)->toBe('HTTPS');
});
