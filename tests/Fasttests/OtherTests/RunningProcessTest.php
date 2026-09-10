<?php

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

// Checks that supervisord/redis/horizon are running as OS processes on this host —
// an environment health-check, not a test of application logic. A CI runner has no
// such process stack (and a Redis service container wouldn't help: service containers
// run in a separate namespace, invisible to `ps` here), so this is excluded from CI.
uses(WithFaker::class)->group('local-daemon');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('check supervisord is running', function () {
    expect(otherTestsProcessExists('supervisord'))->toBeTrue();
});

test('check redis is running', function () {
    expect(otherTestsProcessExists('redis'))->toBeTrue();
});

test('check supervisord is running horizon', function () {
    exec('supervisorctl status', $result);
    $expectedResult = 'horizon';
    expect(implode("\n", $result))->toContain($expectedResult);
});

function otherTestsProcessExists($processName)
{
    $exists = false;
    exec("ps -A | grep -i $processName | grep -v grep", $pids);
    if (count($pids) > 0) {
        $exists = true;
    }

    return $exists;
}
