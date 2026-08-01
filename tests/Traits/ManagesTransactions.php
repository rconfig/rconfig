<?php

namespace Tests\Traits;

/**
 * Wraps a test in a database transaction that is guaranteed to be rolled back.
 *
 * Rolling back is not optional housekeeping. The test suite runs against a
 * shared MySQL server, and a transaction that is left open holds its row locks
 * until the underlying PDO connection is destroyed. PHP only destroys that
 * connection when the previous application graph is garbage collected, which
 * can be many tests later, so an abandoned transaction blocks unrelated tests
 * until they hit innodb_lock_wait_timeout ("Lock wait timeout exceeded").
 *
 * The rollback is registered as a before-application-destroyed callback so a
 * test class does not have to remember to undo it in tearDown.
 */
trait ManagesTransactions
{
    /**
     * Whether this test began a transaction that still needs rolling back.
     */
    private bool $managedTransactionActive = false;

    /**
     * Begin a database transaction for test isolation.
     */
    public function beginTransaction(): void
    {
        if ($this->managedTransactionActive) {
            return;
        }

        $this->app['db']->connection()->beginTransaction();
        $this->managedTransactionActive = true;

        $this->beforeApplicationDestroyed(function (): void {
            $this->rollBackTransaction();
        });
    }

    /**
     * Roll back any open transaction and close the connection so the database
     * server releases its locks immediately. Safe to call more than once.
     */
    public function rollBackTransaction(): void
    {
        $this->managedTransactionActive = false;

        if (! $this->app) {
            return;
        }

        // Only connections that were actually resolved can be holding locks, so
        // this never opens a connection purely to close it again.
        foreach ($this->app['db']->getConnections() as $connection) {
            if ($connection->transactionLevel() === 0) {
                continue;
            }

            // Rolling back fires transaction events; tests that fake the
            // dispatcher should not see them, so mirror Laravel's own trait.
            $dispatcher = $connection->getEventDispatcher();
            $connection->unsetEventDispatcher();

            while ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            $connection->setEventDispatcher($dispatcher);
            $connection->disconnect();
        }
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
