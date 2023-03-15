<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicCommandsTest extends TestCase
{
    protected $user;

    protected $output;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function basic_command_functionality()
    {
        $this->artisan('help')->expectsOutput('Description:')->expectsOutput('Arguments:')->assertExitCode(0);
    }
}
