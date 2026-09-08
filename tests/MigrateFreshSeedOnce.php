<?php

namespace Tests;

use Database\Seeders\testdata\DeviceTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

/**
 * Rebuilds the test schema once per test process.
 *
 * The suite runs against one shared MySQL server (see the `test_mysql` connection and
 * `.env.testing`), not a database per run. `migrate:fresh` drops every table before it
 * rebuilds, so two overlapping test processes destroy each other: the second run drops
 * the tables out from under the first, which then reports missing tables, half built
 * schemas (a table that exists but is missing the columns a later migration adds) and
 * lock wait timeouts, all of them scattered across unrelated tests.
 *
 * A whole run therefore has to be exclusive, not just the migration. Holding the lock
 * only while migrating would still let a second process start dropping tables while the
 * first is midway through its tests. The lock is taken before the first rebuild and held
 * until the process exits.
 *
 * The lock lives on its own PDO connection deliberately. MySQL releases a named lock when
 * the session that holds it closes, and ManagesTransactions disconnects Laravel's
 * connections between tests to release row locks, which would drop the suite lock with
 * them.
 */
trait MigrateFreshSeedOnce
{
    /**
     * If true, setup has run at least once.
     *
     * @var bool
     */
    protected static $setUpHasRunOnce = false;

    /**
     * If true, the Slowtests lab-hardware device fixture has been seeded.
     */
    private static bool $deviceFixturesSeeded = false;

    /**
     * Dedicated connection holding the suite lock for the lifetime of the process.
     */
    private static ?PDO $suiteLockConnection = null;

    /**
     * Seconds to wait for a competing test process to finish before giving up.
     */
    private static int $suiteLockTimeoutSeconds = 900;

    /**
     * After the first run of setUp "migrate:fresh --seed"
     *
     * The lab-hardware device fixture is seeded separately from, and after, the base
     * migrate:fresh/seed above, gated on the test case's real class hierarchy rather than
     * a suite flag: `$setUpHasRunOnce` and `$deviceFixturesSeeded` are independent statics
     * shared by every Tests\TestCase subclass in the process (PHP inherits static
     * storage), so this fires exactly once regardless of whether Fasttests or Slowtests
     * happens to run first, and works whether the suites run combined in one process or
     * filtered separately. Only Slowtests' real SSH/Telnet/ICMP tests are hard-coded
     * against these fixed devices, so no other suite needs them.
     *
     * The seeder call below always runs after acquireSuiteLock() has already fired at
     * least once in this process (either just above, on this same call, or on an earlier
     * test's setUp) and that lock is held for the process's whole lifetime, so this insert
     * is implicitly covered by the same cross-process exclusion the schema rebuild uses.
     * That only holds for a single test process per shared database, which is the
     * assumption the whole file is built on; a future `--parallel` run against the same
     * database would need its own guard around this insert.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$setUpHasRunOnce) {
            static::acquireSuiteLock();

            Artisan::call('migrate:fresh');
            Artisan::call(
                'db:seed',
                ['--class' => 'TestdataDatabaseSeeder']
            );

            static::$setUpHasRunOnce = true;
        }

        if (! static::$deviceFixturesSeeded && is_a(static::class, SlowTestCase::class, true)) {
            Artisan::call('db:seed', ['--class' => DeviceTableSeeder::class]);

            static::$deviceFixturesSeeded = true;
        }
    }

    /**
     * Take the cross process lock that makes this run exclusive on the shared server.
     *
     * Only MySQL offers the named locks this relies on. On any other driver, such as a
     * local SQLite run, there is no shared server to collide over and locking is skipped.
     */
    private static function acquireSuiteLock(): void
    {
        if (self::$suiteLockConnection instanceof PDO) {
            return;
        }

        $name = DB::getDefaultConnection();
        $config = config('database.connections.' . $name);

        if (! is_array($config) || ($config['driver'] ?? null) !== 'mysql') {
            return;
        }

        $connection = self::openSuiteLockConnection($config);

        if (! $connection instanceof PDO) {
            return;
        }

        $lockName = 'rconfig_test_suite_' . ($config['database'] ?? 'unknown');

        $statement = $connection->prepare('SELECT GET_LOCK(?, ?)');
        $statement->execute([$lockName, self::$suiteLockTimeoutSeconds]);

        // GET_LOCK returns 1 on success, 0 on timeout and NULL on error.
        if ((string) $statement->fetchColumn() !== '1') {
            throw new RuntimeException(sprintf(
                'Timed out after %d seconds waiting for the "%s" test suite lock. Another test '
                . 'process is using the shared database "%s". Running two suites at once makes '
                . 'them drop each other\'s tables, so this run stopped instead of corrupting both.',
                self::$suiteLockTimeoutSeconds,
                $lockName,
                $config['database'] ?? 'unknown'
            ));
        }

        // Held for the lifetime of the process. MySQL releases it when this connection
        // closes, which happens on exit, so no explicit teardown is needed.
        self::$suiteLockConnection = $connection;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private static function openSuiteLockConnection(array $config): ?PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '3306'
        );

        try {
            return new PDO(
                $dsn,
                $config['username'] ?? null,
                $config['password'] ?? null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException) {
            // The suite is about to fail on its own connection with a far clearer error
            // than anything raised here, so do not mask it with a lock failure.
            return null;
        }
    }
}
