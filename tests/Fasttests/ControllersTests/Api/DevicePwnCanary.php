<?php

namespace Tests\Fasttests\ControllersTests\Api;

/**
 * Canary "gadget", woken only if the cast instantiates a serialized object.
 */
class DevicePwnCanary
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
