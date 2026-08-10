<?php

namespace Tests\Fasttests\ControllersTests;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Cover for the /download-export endpoint.
 *
 * The filename comes straight off the query string, so the endpoint must only ever
 * serve files that physically sit in the export directory. A caller must not be able
 * to walk up out of that directory, reach an absolute path, or follow a symlink out.
 */
class FileDownloadControllerTest extends TestCase
{
    protected User $user;

    private const EXPORT_FILENAME = 'download_controller_test_export.csv';

    private const EXPORT_CONTENTS = 'id,name' . PHP_EOL . '1,router-01' . PHP_EOL;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        File::ensureDirectoryExists(export_path());
        File::put(export_path() . self::EXPORT_FILENAME, self::EXPORT_CONTENTS);
    }

    protected function tearDown(): void
    {
        File::delete(export_path() . self::EXPORT_FILENAME);

        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_it_downloads_a_file_that_lives_in_the_export_directory(): void
    {
        $response = $this->get('/download-export?filename=' . self::EXPORT_FILENAME . '&type=export');

        $response->assertStatus(200);
        $response->assertDownload(self::EXPORT_FILENAME);
        $this->assertSame(self::EXPORT_CONTENTS, $response->streamedContent());
    }

    public function test_it_refuses_to_traverse_out_of_the_export_directory_to_the_env_file(): void
    {
        $response = $this->get('/download-export?filename=../../../../.env&type=export');

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('APP_KEY', $response->getContent());
        $this->assertStringNotContainsString('DB_PASSWORD', $response->getContent());
    }

    public function test_it_refuses_a_url_encoded_traversal(): void
    {
        $response = $this->get('/download-export?filename=..%2F..%2F..%2F..%2F.env');

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('APP_KEY', $response->getContent());
    }

    public function test_it_refuses_a_deep_traversal_to_a_system_file(): void
    {
        $response = $this->get('/download-export?filename=' . str_repeat('../', 12) . 'etc/passwd');

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('root:', $response->getContent());
    }

    /**
     * The doubled-dot form defeats naive sanitizers that strip '../' once, because
     * stripping the inner sequence from '....//' leaves a working '../' behind.
     */
    public function test_it_refuses_a_doubled_dot_traversal(): void
    {
        $response = $this->get('/download-export?filename=....//....//....//....//.env');

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('APP_KEY', $response->getContent());
    }

    public function test_it_refuses_a_nonexistent_export(): void
    {
        $response = $this->get('/download-export?filename=no_such_export_file.csv');

        $this->assertRequestWasRefused($response);
    }

    public function test_it_refuses_an_absolute_path(): void
    {
        $response = $this->get('/download-export?filename=/etc/passwd');

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('root:', $response->getContent());
    }

    public function test_it_refuses_a_path_that_reaches_a_sibling_storage_directory(): void
    {
        $response = $this->get('/download-export?filename=../logs/laravel.log');

        $this->assertRequestWasRefused($response);
    }

    public function test_it_refuses_a_nested_path_below_the_export_directory(): void
    {
        $nestedDirectory = export_path() . 'nested_download_test';
        File::ensureDirectoryExists($nestedDirectory);
        File::put($nestedDirectory . '/nested.csv', 'nested');

        $response = $this->get('/download-export?filename=nested_download_test/nested.csv');

        $this->assertRequestWasRefused($response);

        File::deleteDirectory($nestedDirectory);
    }

    public function test_it_refuses_a_symlink_that_points_out_of_the_export_directory(): void
    {
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

        $this->assertRequestWasRefused($response);
        $this->assertStringNotContainsString('APP_KEY', $response->getContent());

        unlink($link);
    }

    public function test_it_refuses_a_missing_or_empty_filename(): void
    {
        $this->assertRequestWasRefused($this->get('/download-export'));
        $this->assertRequestWasRefused($this->get('/download-export?filename='));
        $this->assertRequestWasRefused($this->get('/download-export?filename=..'));
    }

    public function test_it_requires_an_authenticated_user(): void
    {
        Auth::logout();

        $response = $this->get('/download-export?filename=' . self::EXPORT_FILENAME);

        $response->assertRedirect('/login');
    }

    /**
     * The endpoint answers a rejected download with the legacy 200 plus error body shape.
     * Assert on the body rather than the status so the check stays meaningful either way.
     */
    private function assertRequestWasRefused(TestResponse $response): void
    {
        $this->assertNotSame(
            'attachment',
            substr((string) $response->headers->get('content-disposition'), 0, 10),
            'Endpoint served a file it should have refused.'
        );

        if ($response->getStatusCode() === 200) {
            $response->assertJsonPath('error', 404);
        }
    }
}
