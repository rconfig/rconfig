<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;

const EXPORT_FILENAME = 'download_controller_test_export.csv';

const EXPORT_CONTENTS = 'id,name' . PHP_EOL . '1,router-01' . PHP_EOL;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    File::ensureDirectoryExists(export_path());
    File::put(export_path() . EXPORT_FILENAME, EXPORT_CONTENTS);
});

afterEach(function () {
    File::delete(export_path() . EXPORT_FILENAME);

    $this->rollBackTransaction();
});

test('it downloads a file that lives in the export directory', function () {
    $response = $this->get('/download-export?filename=' . EXPORT_FILENAME . '&type=export');

    $response->assertStatus(200);
    $response->assertDownload(EXPORT_FILENAME);
    expect($response->streamedContent())->toBe(EXPORT_CONTENTS);
});

test('it refuses to traverse out of the export directory to the env file', function () {
    $response = $this->get('/download-export?filename=../../../../.env&type=export');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('APP_KEY', $response->getContent());
    $this->assertStringNotContainsString('DB_PASSWORD', $response->getContent());
});

test('it refuses a url encoded traversal', function () {
    $response = $this->get('/download-export?filename=..%2F..%2F..%2F..%2F.env');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('APP_KEY', $response->getContent());
});

test('it refuses a deep traversal to a system file', function () {
    $response = $this->get('/download-export?filename=' . str_repeat('../', 12) . 'etc/passwd');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('root:', $response->getContent());
});

test('it refuses a doubled dot traversal', function () {
    $response = $this->get('/download-export?filename=....//....//....//....//.env');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('APP_KEY', $response->getContent());
});

test('it refuses a nonexistent export', function () {
    $response = $this->get('/download-export?filename=no_such_export_file.csv');

    assertRequestWasRefused($response);
});

test('it refuses an absolute path', function () {
    $response = $this->get('/download-export?filename=/etc/passwd');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('root:', $response->getContent());
});

test('it refuses a path that reaches a sibling storage directory', function () {
    $response = $this->get('/download-export?filename=../logs/laravel.log');

    assertRequestWasRefused($response);
});

test('it refuses a nested path below the export directory', function () {
    $nestedDirectory = export_path() . 'nested_download_test';
    File::ensureDirectoryExists($nestedDirectory);
    File::put($nestedDirectory . '/nested.csv', 'nested');

    $response = $this->get('/download-export?filename=nested_download_test/nested.csv');

    assertRequestWasRefused($response);

    File::deleteDirectory($nestedDirectory);
});

test('it refuses a symlink that points out of the export directory', function () {
    $target = base_path('.env');
    if (! File::exists($target)) {
        $this->markTestSkipped('No .env file present to link against.');
    }

    $link = export_path() . 'download_controller_test_link';
    @symlink($target, $link);
    if (! is_link($link)) {
        $this->markTestSkipped('Unable to create a symlink in the export directory.');
    }

    $response = $this->get('/download-export?filename=download_controller_test_link');

    assertRequestWasRefused($response);
    $this->assertStringNotContainsString('APP_KEY', $response->getContent());

    unlink($link);
});

test('it refuses a missing or empty filename', function () {
    assertRequestWasRefused($this->get('/download-export'));
    assertRequestWasRefused($this->get('/download-export?filename='));
    assertRequestWasRefused($this->get('/download-export?filename=..'));
});

test('it requires an authenticated user', function () {
    Auth::logout();

    $response = $this->get('/download-export?filename=' . EXPORT_FILENAME);

    $response->assertRedirect('/login');
});

/**
 * The endpoint answers a rejected download with the legacy 200 plus error body shape.
 * Assert on the body rather than the status so the check stays meaningful either way.
 */
function assertRequestWasRefused(TestResponse $response): void
{
    Assert::assertNotSame(
        'attachment',
        substr((string) $response->headers->get('content-disposition'), 0, 10),
        'Endpoint served a file it should have refused.'
    );

    if ($response->getStatusCode() === 200) {
        $response->assertJsonPath('error', 404);
    }
}
