<?php

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
