<?php

namespace Tests\Fasttests\ServiceTests\ConfigHistory;

use App\Models\Config;
use App\Services\ConfigHistory\ConfigHistoryManager;
use Tests\TestCase;

class ConfigHistoryManagerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    /**
     * Build a comparer factory whose version_compare() returns a fixed value
     * (or throws), so the manager's orchestration can be tested in isolation.
     */
    private function factoryReturning(bool $result): callable
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

    public function test_it_returns_true_when_version_compare_returns_true(): void
    {
        $manager = new ConfigHistoryManager($this->factoryReturning(true));

        $this->assertTrue($manager->handleNewDownloadedConfig(new Config, 'show run'));
    }

    public function test_it_returns_false_when_version_compare_returns_false(): void
    {
        $manager = new ConfigHistoryManager($this->factoryReturning(false));

        $this->assertFalse($manager->handleNewDownloadedConfig(new Config, 'show run'));
    }

    public function test_it_propagates_exceptions_from_version_compare(): void
    {
        $this->expectException(\RuntimeException::class);

        $factory = function () {
            return new class
            {
                public function version_compare(): bool
                {
                    throw new \RuntimeException('boom');
                }
            };
        };

        (new ConfigHistoryManager($factory))->handleNewDownloadedConfig(new Config, 'show run');
    }
}
