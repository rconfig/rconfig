<?php

use App\Http\Controllers\Api\ExportController;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('it can get the list of exportable tables', function () {
    $response = $this->get('/api/settings/export/list-tables');
    $response->assertStatus(200);
    expect($response->json()['data'])->toHaveKey('tables');
    expect($response->json()['data']['tables'])->toContain('activity_log');
    expect($response->json()['data']['tables'])->toContain('devices');
    expect($response->json()['data']['tables'])->toContain('users');
    expect($response->json()['data']['tables'])->not->toContain('migrations');
    expect($response->json()['data']['tables'])->not->toContain('sessions');
    expect($response->json()['data']['tables'])->not->toContain('jobs');

    // check that the list of tables matches the expected list based on the excluded tables
    $expected = array_values(array_diff(
        ExportController::listAllBaseTables(),
        ExportController::EXCLUDED_TABLES
    ));
    $response->assertJsonCount(count($expected), 'data.tables');
    expect($response->json('data.tables'))->toEqualCanonicalizing($expected);
});

test('it can export a table', function () {
    // https://docs.laravel-excel.com/3.1/exports/testing.html#testing-downloads
    $table = 'users';

    $response = $this->get('/api/settings/export/get-table/' . $table)
        ->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'downloadLink',
            'downloadUrl',
            'filename',
        ],
    ]);
    $response->assertJsonFragment([
        'downloadUrl' => '/download-export?filename=users.csv&type=export',
        'filename' => 'users.csv',
    ]);
});
