<?php

use App\Http\Controllers\Api\ScheduleController;

test('list returns json with scheduled tasks', function () {
    $controller = new ScheduleController;
    $response = $controller->list();

    expect($response->getStatusCode())->toEqual(200);

    $data = json_decode($response->getContent(), true);

    expect($data['success'])->toBeTrue();
    expect($data)->toHaveKey('scheduled_tasks');
    expect($data['scheduled_tasks'])->toBeArray();
});

test('list includes timezone information', function () {
    $controller = new ScheduleController;

    $response = $controller->list();
    $data = json_decode($response->getContent(), true);

    if (! empty($data['scheduled_tasks'])) {
        $task = $data['scheduled_tasks'][0];
        expect($task['timezone'])->not->toBeEmpty();
    } else {
        expect(true)->toBeTrue(); // No tasks to test
    }
});
