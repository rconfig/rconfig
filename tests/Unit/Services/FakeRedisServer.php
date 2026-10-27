<?php

namespace Tests\Unit\Services;

/**
 * An in-memory stand-in for a Redis server. Lets the service be exercised end to
 * end without ever touching a live instance (this box shares one Redis across
 * several apps, so a real requirepass here would lock them out).
 */
class FakeRedisServer
{
    public bool $up = true;
    public ?string $requirepass = null;
    public string $bind = '127.0.0.1';
    public string $protectedMode = 'yes';
    public bool $rewriteFails = false;
}
