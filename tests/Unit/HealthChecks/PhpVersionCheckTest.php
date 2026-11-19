<?php

use App\HealthChecks\PhpVersionCheck;
use Spatie\Health\Enums\Status;
use Spatie\Health\Facades\Health;

test('it is ok at or above the minimum PHP version', function (string $version) {
    config(['rConfig.min_php_version' => '8.4.28']);

    $result = app(PhpVersionCheck::class)->phpVersion($version)->run();

    expect($result->status->equals(Status::ok()))->toBeTrue()
        ->and($result->getShortSummary())->toBe($version)
        ->and($result->meta)->toBe(['php_version' => $version, 'minimum' => '8.4.28']);
})->with(['8.4.28', '8.4.29', '8.5.0']);

test('it warns, and never fails, below the minimum PHP version', function (string $version) {
    config(['rConfig.min_php_version' => '8.4.28']);

    $result = app(PhpVersionCheck::class)->phpVersion($version)->run();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getShortSummary())->toBe('Upgrade PHP')
        ->and($result->getNotificationMessage())->toContain("PHP {$version} is older than 8.4.28")
        ->and($result->getNotificationMessage())->toContain('Upgrade PHP to 8.4.28 or later');
})->with(['8.4.27', '8.3.36', '8.2.35']);

test('it reads the running PHP version when none is given', function () {
    $result = app(PhpVersionCheck::class)->run();

    expect($result->meta['php_version'])->toBe(PHP_VERSION);
});

test('the minimum matches the one update.sh enforces', function () {
    preg_match('/^MIN_PHP_VERSION="([^"]+)"$/m', file_get_contents(base_path('update.sh')), $match);

    expect(config('rConfig.min_php_version'))->toBe('8.4.28')
        ->and($match[1] ?? null)->toBe(config('rConfig.min_php_version'));
});

test('it is registered as PHP Version', function () {
    $check = collect(Health::registeredChecks())->first(fn ($check) => $check instanceof PhpVersionCheck);

    expect($check)->not->toBeNull()
        ->and($check->getName())->toBe('PHP Version');
});
