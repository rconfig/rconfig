<?php

use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;

/**
 * @param  array<string, mixed>  $switches
 * @return list<string>
 */
function trace(array $switches): array
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

test('switches all off take the standard read', function () {
    expect(trace([]))->toBe(['send.sendString', 'connection.read']);
});

test('non interactive mode off no longer takes the exec branch', function () {
    expect(trace(['isNonInteractiveMode' => 'off']))->not->toContain('send.sendStringExec');
});

test('non interactive mode on takes the exec branch', function () {
    expect(trace(['isNonInteractiveMode' => 'on']))->toContain('send.sendStringExec');
});

test('ansi host on takes the ansi branch', function () {
    expect(trace(['AnsiHost' => 'on']))->toBe(['connection.read', 'send.sendString', 'connection.read']);
});

test('ansi host off does not take the ansi branch', function () {
    expect(trace(['AnsiHost' => 'off']))->toBe(['send.sendString', 'connection.read']);
});

test('mikrotik on reads once before the standard read', function () {
    expect(trace(['isMikrotik' => 'on']))->toBe(['connection.read', 'send.sendString', 'connection.read']);
});

test('mikrotik off does not read first', function () {
    expect(trace(['isMikrotik' => 'off']))->toBe(['send.sendString', 'connection.read']);
});
