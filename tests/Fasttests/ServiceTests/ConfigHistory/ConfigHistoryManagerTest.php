<?php

use App\Models\Config;
use App\Services\ConfigHistory\ConfigHistoryManager;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * Build a comparer factory whose version_compare() returns a fixed value
 * (or throws), so the manager's orchestration can be tested in isolation.
 */
function factoryReturning(bool $result): callable
{
    return function () use ($result) {
        return new class($result)
        {
            public function __construct(private bool $result) {}

            public function version_compare(): bool
            {
                return $this->result;
            }
        };
    };
}

test('it returns true when version compare returns true', function () {
    $manager = new ConfigHistoryManager(factoryReturning(true));

    expect($manager->handleNewDownloadedConfig(new Config, 'show run'))->toBeTrue();
});

test('it returns false when version compare returns false', function () {
    $manager = new ConfigHistoryManager(factoryReturning(false));

    expect($manager->handleNewDownloadedConfig(new Config, 'show run'))->toBeFalse();
});

test('it propagates exceptions from version compare', function () {
    $this->expectException(RuntimeException::class);

    $factory = function () {
        return new class
        {
            public function version_compare(): bool
            {
                throw new RuntimeException('boom');
            }
        };
    };

    (new ConfigHistoryManager($factory))->handleNewDownloadedConfig(new Config, 'show run');
});
