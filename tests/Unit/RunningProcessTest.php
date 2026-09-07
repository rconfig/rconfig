<?php

// Checks that supervisord/redis/horizon are running as OS processes on this host —
// an environment health-check, not a test of application logic. A CI runner has no
// such process stack (and a Redis service container wouldn't help: service containers
// run in a separate namespace, invisible to `ps` here), so this is excluded from CI.
uses()->group('local-daemon');

test('check supervisord is running', function () {
    expect(processExists('supervisord'))->toBeTrue();
});

test('check redis is running', function () {
    expect(processExists('redis'))->toBeTrue();
});

test('check supervisord is running horizon', function () {
    exec('supervisorctl status', $result);
    $expectedResult = 'horizon';
    expect($result[0])->toContain($expectedResult);
});

function processExists($processName)
{
    $exists = false;
    exec("ps -A | grep -i $processName | grep -v grep", $pids);
    if (count($pids) > 0) {
        $exists = true;
    }

    return $exists;
}
