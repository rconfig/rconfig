<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait ManagesTransactions
{
    /**
     * Begin a database transaction for test isolation.
     */
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * Roll back the database transaction.
     */
    public function rollBackTransaction(): void
    {
        DB::rollBack();
    }

    /**
     * Set up automatic transaction management.
     * Call this in your test's setUp() method.
     */
    protected function setUpTransactions(): void
    {
        $this->beginTransaction();
    }

    /**
     * Clean up automatic transaction management.
     * Call this in your test's tearDown() method.
     */
    protected function tearDownTransactions(): void
    {
        $this->rollBackTransaction();
    }
}
