<?php

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

/**
 * Guards the test harness itself.
 *
 * Tests wrap themselves in a transaction for isolation. If one is left open the
 * connection keeps its row locks alive on the shared MySQL server long after
 * the test finished, and unrelated tests later in the run fail with
 * "Lock wait timeout exceeded". These tests prove the harness always closes the
 * transaction out, even when a test forgets to.
 *
 * The tests run in declaration order, which matters: the first one
 * deliberately leaks, the second one checks the harness cleaned up after it.
 */

/**
 * Seconds a lock probe waits before giving up. Well under the server's
 * default innodb_lock_wait_timeout so a regression fails fast.
 */
const LOCK_PROBE_TIMEOUT = 3;

test('a transaction left open by a test still holds a row lock', function () {
    $this->beginTransaction();

    DB::table('vendors')->where('id', lockProbeVendorId())->lockForUpdate()->first();

    expect(DB::transactionLevel())->toBe(1);

    // Deliberately no rollback here. The harness must clean this up.
});

test('b the next test starts clean and is not blocked', function () {
    expect(DB::transactionLevel())->toBe(0, 'A previous test leaked an open transaction.');

    DB::statement('SET SESSION innodb_lock_wait_timeout = ' . LOCK_PROBE_TIMEOUT);

    $this->beginTransaction();

    // Times out instead of succeeding if the lock from the previous test is
    // still held by an abandoned connection.
    $row = DB::table('vendors')->where('id', lockProbeVendorId())->lockForUpdate()->first();

    expect($row)->not->toBeNull();

    $this->rollBackTransaction();
});

test('rolling back discards writes and closes the transaction', function () {
    $this->beginTransaction();

    $vendor = Vendor::factory()->create();

    $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);

    $this->rollBackTransaction();

    expect(DB::transactionLevel())->toBe(0);
    $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
});

test('rolling back is safe to call without an open transaction', function () {
    $this->rollBackTransaction();
    $this->rollBackTransaction();

    expect(DB::transactionLevel())->toBe(0);
});

/**
 * A stable, seeded row both lock probes can contend over.
 */
function lockProbeVendorId(): int
{
    return (int) DB::table('vendors')->orderBy('id')->value('id');
}
