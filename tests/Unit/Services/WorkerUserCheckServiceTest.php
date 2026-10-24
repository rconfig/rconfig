<?php

use App\Services\System\WorkerUserCheckService;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

beforeEach(function () {
    $this->host = $this->fakeHostFilesystem();
    $this->host->userNames = [0 => 'root', 48 => 'apache'];
});

test('it finds this install\'s Horizon master with its user', function () {
    $this->host->processList = [
        ['pid' => 10, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon']],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses())->toBe([[
        'pid' => 10,
        'uid' => 48,
        'user' => 'apache',
        'command' => 'php ' . base_path('artisan') . ' horizon',
    ]]);
});

test('it skips other app folders, Horizon subcommands and unrelated processes', function () {
    $this->host->processList = [
        ['pid' => 11, 'uid' => 0, 'arguments' => ['php', '/var/www/html/rconfig8/artisan', 'horizon']],
        ['pid' => 12, 'uid' => 0, 'arguments' => ['php', base_path() . '8/artisan', 'horizon']],
        ['pid' => 13, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon:work', 'redis']],
        ['pid' => 14, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon:supervisor']],
        ['pid' => 15, 'uid' => 0, 'arguments' => ['/usr/bin/redis-server', '127.0.0.1:6379']],
        ['pid' => 16, 'uid' => 0, 'arguments' => []],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses())->toBe([]);
});

test('it falls back to the uid when the user has no name', function () {
    $this->host->processList = [
        ['pid' => 17, 'uid' => 4242, 'arguments' => ['php', base_path('artisan'), 'horizon']],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses()[0]['user'])->toBe('4242');
});

test('the expected user is the web user', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Ubuntu\"\nID=ubuntu\n");

    expect(app(WorkerUserCheckService::class)->expectedUser())->toBe(['www-data', 'www-data']);
});
