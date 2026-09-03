<?php

namespace Tests\Unit\Console\Commands;

use Tests\UnitTestCase;

class BasicCommandsTest extends UnitTestCase
{
    protected $user;
    protected $output;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_basic_command_functionality()
    {
        $this->artisan('help')->expectsOutput('Description:')->expectsOutput('Arguments:')->assertExitCode(0);
    }
}
