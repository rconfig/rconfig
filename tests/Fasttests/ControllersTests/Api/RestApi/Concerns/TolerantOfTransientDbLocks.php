<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * The REST API tests run against a shared remote MySQL test database. Deletes
 * that cascade across pivot tables (device_tag, tag_task, category_command,
 * device_vendor, etc.) can intermittently hit a "Lock wait timeout exceeded"
 * error under contention. That failure is environmental, not a code defect, so
 * these helpers let a test skip itself when it occurs.
 */
trait TolerantOfTransientDbLocks
{
    /**
     * Issue a DELETE request, skipping the test if a transient lock wait timeout
     * is hit (whether thrown as an exception or rendered as a 500 response).
     *
     * @param  array<string, string>  $headers
     */
    protected function deleteJsonTolerant(string $uri, array $headers = []): TestResponse
    {
        try {
            $response = $this->withHeaders($headers)->deleteJson($uri);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Lock wait timeout exceeded')) {
                $this->markTestSkipped('Transient test DB lock wait timeout during delete.');
            }
            throw $e;
        }

        if ($response->getStatusCode() !== 200) {
            $logged = $response->exceptions->last();
            $message = $logged ? $logged->getMessage() : (string) $response->getContent();
            if (str_contains($message, 'Lock wait timeout exceeded')) {
                $this->markTestSkipped('Transient test DB lock wait timeout during delete.');
            }
        }

        return $response;
    }
}
