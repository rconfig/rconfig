<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base for pure unit tests that boot the application but never touch the
 * database, so they skip the migrate-once/transaction machinery Tests\TestCase
 * carries for Fasttests/Slowtests.
 */
abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;
}
