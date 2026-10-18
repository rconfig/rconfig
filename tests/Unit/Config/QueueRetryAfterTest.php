<?php

/**
 * Laravel releases a reserved Redis job back onto the queue once retry_after
 * seconds have passed. If a Horizon supervisor's worker timeout is not below the
 * retry_after of the connection it uses, a long-running job is picked up a second
 * time while it is still running (and, with tries = 1, fails as attempted too
 * many times).
 */
test('every horizon supervisor uses a connection whose retry_after exceeds its timeout', function () {
    $checked = 0;

    foreach (config('horizon.environments') as $environment => $supervisors) {
        foreach ($supervisors as $name => $supervisor) {
            $connection = config('queue.connections.' . $supervisor['connection']);

            expect($connection)->not->toBeNull("{$environment}.{$name} uses an unknown queue connection");
            expect($connection['retry_after'])->toBeGreaterThan(
                $supervisor['timeout'],
                "{$environment}.{$name} timeout ({$supervisor['timeout']}s) must be lower than retry_after of '{$supervisor['connection']}' ({$connection['retry_after']}s)"
            );

            $checked++;
        }
    }

    expect($checked)->toBeGreaterThan(0);
});

test('the long-running queue connection shares the default redis queues', function () {
    $default = config('queue.connections.redis');
    $longRunning = config('queue.connections.redis-long-running');

    expect($longRunning['driver'])->toBe($default['driver'])
        ->and($longRunning['connection'])->toBe($default['connection'])
        ->and($longRunning['queue'])->toBe($default['queue']);
});
