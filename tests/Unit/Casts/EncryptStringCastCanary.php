<?php

namespace Tests\Unit\Casts;

/**
 * Canary "gadget". If EncryptStringCast ever instantiates a serialized object, __wakeup()
 * (or __destruct() on a real instance) flips the static flag and the security test fails.
 */
class EncryptStringCastCanary
{
    public static bool $awoke = false;

    public function __wakeup(): void
    {
        self::$awoke = true;
    }

    public function __destruct()
    {
        self::$awoke = true;
    }
}
