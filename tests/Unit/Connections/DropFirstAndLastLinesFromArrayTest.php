<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;
use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;
use App\Http\Controllers\Connections\Telnet\SendCommand as TelnetSendCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Both connection paths strip the echoed command from the front of the command
 * output and the trailing prompt from the back. Those are positional guesses, so
 * on short output they used to consume every line and hand back an empty array,
 * which was then persisted as an empty config and reported as a good backup.
 *
 * These classes hold a live socket, so they are built without their constructors
 * and driven through their line handling alone.
 */
class DropFirstAndLastLinesFromArrayTest extends TestCase
{
    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    private function dropViaSsh(array $lines): array
    {
        $reflection = new ReflectionClass(SshSendCommand::class);
        $sendCommand = $reflection->newInstanceWithoutConstructor();

        $data = $reflection->getProperty('data');
        $data->setValue($sendCommand, $lines);

        $reflection->getMethod('dropFirstAndLastLinesFromArray')->invoke($sendCommand);

        return $data->getValue($sendCommand);
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    private function dropViaTelnet(array $lines): array
    {
        $readReflection = new ReflectionClass(TelnetRead::class);
        $read = $readReflection->newInstanceWithoutConstructor();

        $readData = $readReflection->getProperty('data');
        $readData->setValue($read, $lines);

        $reflection = new ReflectionClass(TelnetSendCommand::class);
        $sendCommand = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('read')->setValue($sendCommand, $read);

        $reflection->getMethod('dropFirstAndLastLinesFromArray')->invoke($sendCommand);

        return $readData->getValue($read);
    }

    public function test_ssh_keeps_single_line_output(): void
    {
        $this->assertSame(['hostname router1'], $this->dropViaSsh(['hostname router1']));
    }

    public function test_ssh_keeps_two_line_output(): void
    {
        $this->assertSame(
            ['show run', 'hostname router1'],
            $this->dropViaSsh(['show run', 'hostname router1'])
        );
    }

    public function test_ssh_strips_command_echo_and_prompt_when_content_survives(): void
    {
        $this->assertSame(
            ['hostname router1'],
            $this->dropViaSsh(['show run', 'hostname router1', 'router1#'])
        );
    }

    public function test_ssh_strips_only_the_outer_lines_of_longer_output(): void
    {
        $this->assertSame(
            ['version 15.2', 'hostname router1', 'interface Gi0/0'],
            $this->dropViaSsh(['show run', 'version 15.2', 'hostname router1', 'interface Gi0/0', 'router1#'])
        );
    }

    public function test_ssh_handles_empty_output(): void
    {
        $this->assertSame([], $this->dropViaSsh([]));
    }

    public function test_telnet_keeps_single_line_output(): void
    {
        $this->assertSame(['hostname router1'], $this->dropViaTelnet(['hostname router1']));
    }

    public function test_telnet_keeps_two_line_output(): void
    {
        $this->assertSame(
            ['show run', 'hostname router1'],
            $this->dropViaTelnet(['show run', 'hostname router1'])
        );
    }

    public function test_telnet_strips_command_echo_and_prompt_when_content_survives(): void
    {
        $this->assertSame(
            ['hostname router1'],
            $this->dropViaTelnet(['show run', 'hostname router1', 'router1#'])
        );
    }

    public function test_telnet_strips_only_the_outer_lines_of_longer_output(): void
    {
        $this->assertSame(
            ['version 15.2', 'hostname router1', 'interface Gi0/0'],
            $this->dropViaTelnet(['show run', 'version 15.2', 'hostname router1', 'interface Gi0/0', 'router1#'])
        );
    }

    public function test_telnet_handles_empty_output(): void
    {
        $this->assertSame([], $this->dropViaTelnet([]));
    }
}
