<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\Telnet\Connect as TelnetConnect;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Both connection classes carried a port guard whose two branches returned the same
 * value, and whose return value the caller threw away. A template or device override
 * holding a null or out of range port was handed to the socket layer as written, so
 * the guard's name promised a check it never made.
 *
 * The classes hold a live socket, so they are built without their constructors and
 * driven through the port resolution alone.
 */
class ConnectPortValidationTest extends TestCase
{
    private function resolveSshPort(mixed $port): int
    {
        $reflection = new ReflectionClass(SshConnect::class);
        $connect = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('port')->setValue($connect, $port);

        return $reflection->getMethod('sshPortValidOrDefault')->invoke($connect);
    }

    private function resolveTelnetPort(mixed $port): int
    {
        $reflection = new ReflectionClass(TelnetConnect::class);
        $connect = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('port')->setValue($connect, $port);

        return $reflection->getMethod('telnetPortValidOrDefault')->invoke($connect);
    }

    public function test_ssh_keeps_a_valid_port(): void
    {
        $this->assertSame(2222, $this->resolveSshPort(2222));
    }

    public function test_ssh_keeps_a_valid_port_given_as_a_string(): void
    {
        $this->assertSame(22, $this->resolveSshPort('22'));
    }

    public function test_ssh_defaults_a_null_port(): void
    {
        $this->assertSame(22, $this->resolveSshPort(null));
    }

    public function test_ssh_defaults_an_empty_port(): void
    {
        $this->assertSame(22, $this->resolveSshPort(''));
    }

    public function test_ssh_defaults_a_zero_or_negative_port(): void
    {
        $this->assertSame(22, $this->resolveSshPort(0));
        $this->assertSame(22, $this->resolveSshPort(-1));
    }

    public function test_ssh_defaults_a_port_above_the_valid_range(): void
    {
        $this->assertSame(22, $this->resolveSshPort(70000));
    }

    public function test_telnet_keeps_a_valid_port(): void
    {
        $this->assertSame(2323, $this->resolveTelnetPort(2323));
    }

    public function test_telnet_keeps_a_valid_port_given_as_a_string(): void
    {
        $this->assertSame(23, $this->resolveTelnetPort('23'));
    }

    public function test_telnet_defaults_a_null_port(): void
    {
        $this->assertSame(23, $this->resolveTelnetPort(null));
    }

    public function test_telnet_defaults_an_empty_port(): void
    {
        $this->assertSame(23, $this->resolveTelnetPort(''));
    }

    public function test_telnet_defaults_a_zero_or_negative_port(): void
    {
        $this->assertSame(23, $this->resolveTelnetPort(0));
        $this->assertSame(23, $this->resolveTelnetPort(-1));
    }

    public function test_telnet_defaults_a_port_above_the_valid_range(): void
    {
        $this->assertSame(23, $this->resolveTelnetPort(70000));
    }

    /**
     * The resolved port has to reach the socket, which is what the old code missed.
     * SSH2 does not dial on construction, so the whole of connect() can run here.
     */
    public function test_ssh_connect_dials_the_resolved_port(): void
    {
        $connect = new SshConnect(DeviceParamsBuilder::forSsh(['connect' => ['port' => 0]]), false);
        $connect->connect();

        $this->assertSame(22, $connect->port);
    }

    public function test_ssh_connect_keeps_a_valid_port(): void
    {
        $connect = new SshConnect(DeviceParamsBuilder::forSsh(['connect' => ['port' => 2222]]), false);
        $connect->connect();

        $this->assertSame(2222, $connect->port);
    }
}
