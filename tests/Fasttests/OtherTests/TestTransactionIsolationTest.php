<?php

namespace Tests\Fasttests\OtherTests;

use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guards the test harness itself.
 *
 * Tests wrap themselves in a transaction for isolation. If one is left open the
 * connection keeps its row locks alive on the shared MySQL server long after
 * the test finished, and unrelated tests later in the run fail with
 * "Lock wait timeout exceeded". These tests prove the harness always closes the
 * transaction out, even when a test forgets to.
 *
 * The methods run in declaration order, which matters: the first one
 * deliberately leaks, the second one checks the harness cleaned up after it.
 */
class TestTransactionIsolationTest extends TestCase
{
    /**
     * Seconds a lock probe waits before giving up. Well under the server's
     * default innodb_lock_wait_timeout so a regression fails fast.
     */
    private const LOCK_PROBE_TIMEOUT = 3;

    public function test_a_transaction_left_open_by_a_test_still_holds_a_row_lock(): void
    {
        $this->beginTransaction();

        DB::table('vendors')->where('id', $this->lockProbeVendorId())->lockForUpdate()->first();

        $this->assertSame(1, DB::transactionLevel());

        // Deliberately no rollback here. The harness must clean this up.
    }

    public function test_b_the_next_test_starts_clean_and_is_not_blocked(): void
    {
        $this->assertSame(0, DB::transactionLevel(), 'A previous test leaked an open transaction.');

        DB::statement('SET SESSION innodb_lock_wait_timeout = ' . self::LOCK_PROBE_TIMEOUT);

        $this->beginTransaction();

        // Times out instead of succeeding if the lock from the previous test is
        // still held by an abandoned connection.
        $row = DB::table('vendors')->where('id', $this->lockProbeVendorId())->lockForUpdate()->first();

        $this->assertNotNull($row);

        $this->rollBackTransaction();
    }

    public function test_rolling_back_discards_writes_and_closes_the_transaction(): void
    {
        $this->beginTransaction();

        $vendor = Vendor::factory()->create();

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);

        $this->rollBackTransaction();

        $this->assertSame(0, DB::transactionLevel());
        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }

    public function test_rolling_back_is_safe_to_call_without_an_open_transaction(): void
    {
        $this->rollBackTransaction();
        $this->rollBackTransaction();

        $this->assertSame(0, DB::transactionLevel());
    }

    /**
     * A stable, seeded row both lock probes can contend over.
     */
    private function lockProbeVendorId(): int
    {
        return (int) DB::table('vendors')->orderBy('id')->value('id');
    }
}
