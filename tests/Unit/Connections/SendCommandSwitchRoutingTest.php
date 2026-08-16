<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The SSH send path picks between exec, ANSI and standard reads from template switches.
 * `isNonInteractiveMode` used to be read as a bare truthy value, so the string 'off' took
 * the exec branch, and `AnsiHost` / `isMikrotik` were compared against 'yes' while every
 * other switch was compared against 'on'. TemplateNormaliser settles all of them on
 * 'on' / 'off', so these assert the branch each canonical value now takes.
 *
 * SendCommand holds a live socket, so it is built without its constructor and driven
 * through recording doubles.
 */
class SendCommandSwitchRoutingTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $switches
     * @return list<string>
     */
    private function trace(array $switches): array
    {
        $trace = [];

        $connection = new class($trace)
        {
            /**
             * @param  list<string>  $trace
             */
            public function __construct(private array &$trace) {}

            public function read(...$arguments): string
            {
                $this->trace[] = 'connection.read';

                return "show run\r\nhostname r1\r\nr1#";
            }
        };

        $send = new class($trace)
        {
            /**
             * @param  list<string>  $trace
             */
            public function __construct(private array &$trace) {}

            public function sendString($command): void
            {
                $this->trace[] = 'send.sendString';
            }

            public function sendStringExec($command): string
            {
                $this->trace[] = 'send.sendStringExec';

                return "hostname r1\r\n";
            }
        };

        $connectionObj = (object) array_merge([
            'connection' => $connection,
            'devicePrompt' => 'r1#',
            'sshPrivKey' => null,
            'isNonInteractiveMode' => 'off',
            'AnsiHost' => 'off',
            'isMikrotik' => 'off',
            'hpAnyKeyStatus' => 'off',
            'setTerminalDimensions' => null,
        ], $switches);

        $reflection = new ReflectionClass(SshSendCommand::class);
        $sendCommand = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('connectionObj')->setValue($sendCommand, $connectionObj);
        $reflection->getProperty('send')->setValue($sendCommand, $send);

        $sendCommand->sendShowCommand('show run');

        return $trace;
    }

    public function test_switches_all_off_take_the_standard_read(): void
    {
        $this->assertSame(['send.sendString', 'connection.read'], $this->trace([]));
    }

    public function test_non_interactive_mode_off_no_longer_takes_the_exec_branch(): void
    {
        $this->assertNotContains('send.sendStringExec', $this->trace(['isNonInteractiveMode' => 'off']));
    }

    public function test_non_interactive_mode_on_takes_the_exec_branch(): void
    {
        $this->assertContains('send.sendStringExec', $this->trace(['isNonInteractiveMode' => 'on']));
    }

    public function test_ansi_host_on_takes_the_ansi_branch(): void
    {
        $this->assertSame(
            ['connection.read', 'send.sendString', 'connection.read'],
            $this->trace(['AnsiHost' => 'on'])
        );
    }

    public function test_ansi_host_off_does_not_take_the_ansi_branch(): void
    {
        $this->assertSame(['send.sendString', 'connection.read'], $this->trace(['AnsiHost' => 'off']));
    }

    public function test_mikrotik_on_reads_once_before_the_standard_read(): void
    {
        $this->assertSame(
            ['connection.read', 'send.sendString', 'connection.read'],
            $this->trace(['isMikrotik' => 'on'])
        );
    }

    public function test_mikrotik_off_does_not_read_first(): void
    {
        $this->assertSame(['send.sendString', 'connection.read'], $this->trace(['isMikrotik' => 'off']));
    }
}
