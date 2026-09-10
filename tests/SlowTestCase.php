<?php

namespace Tests;

/**
 * Marker subclass bound to the Slowtests suite in tests/Pest.php.
 *
 * It exists so Slowtests test cases can be identified by their real place in the class
 * hierarchy (`is_a(static::class, self::class, true)`), which holds for both classic
 * PHPUnit subclasses and Pest-generated classes alike, rather than by sniffing
 * static::class's namespace or Pest's internal file-tracking properties.
 */
abstract class SlowTestCase extends TestCase
{
    //
}
