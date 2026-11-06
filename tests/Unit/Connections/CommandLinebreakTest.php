<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\SSH\Send;
use App\Http\Controllers\Connections\SSH\SendCommand;
use Tests\Unit\Connections\DeviceParamsBuilder;

/**
 * Covers the opt-in config.commandLinebreak template setting: the line ending Send::sendString()
 * appends to every command written to the interactive shell. Force10/FTOS ignores a bare "\r",
 * so its template opts in to "\n". Absent or unrecognised values must keep the historical "\r"
 * so existing templates are untouched. Ported from Pro RCO-1546, see RCO-1588.
 */
const LINEBREAK_TEST_COMMAND = 'show running-config';

/**
 * Records what Send writes, in place of the phpseclib connection.
 */
function linebreakRecordingConnection(): object
{
    return new class
    {
        /** @var list<string> */
        public array $writes = [];

        /** @var list<string> */
        public array $execs = [];

        public function write(string $data): bool
        {
            $this->writes[] = $data;

            return true;
        }

        public function exec(string $command): string
        {
            $this->execs[] = $command;

            return '';
        }

        public function read(...$arguments): string
        {
            return '';
        }
    };
}

/**
 * @param  array<string, mixed>  $config  merged into the template config section, a null value removes the key
 */
function linebreakSshConnectWith(array $config): SshConnect
{
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['config' => $config]), false);
    $connect->connection = linebreakRecordingConnection();

    return $connect;
}

dataset('command linebreaks', [
    'key absent' => [null, "\r"],
    "'r'" => ['r', "\r"],
    "'n'" => ['n', "\n"],
    "'rn'" => ['rn', "\r\n"],
    "uppercase 'N'" => ['N', "\n"],
    "padded ' n '" => [' n ', "\n"],
    "uppercase 'RN'" => ['RN', "\r\n"],
    "invalid 'crlf'" => ['crlf', "\r"],
    'empty string' => ['', "\r"],
    'boolean true' => [true, "\r"],
    'integer 1' => [1, "\r"],
]);

test('send string appends the configured line ending', function (mixed $commandLinebreak, string $expectedEnding) {
    $connect = linebreakSshConnectWith(['commandLinebreak' => $commandLinebreak]);

    (new Send($connect))->sendString(LINEBREAK_TEST_COMMAND);

    expect($connect->connection->writes)->toBe([LINEBREAK_TEST_COMMAND . $expectedEnding]);
})->with('command linebreaks');

test('reset paging command uses the configured line ending', function () {
    $connect = linebreakSshConnectWith(['commandLinebreak' => 'n']);

    (new SendCommand($connect))->resetPagingCommand();

    expect($connect->connection->writes)->toBe(["terminal length 24\n"]);
});

test('exec channel keeps carriage return regardless of setting', function () {
    $connect = linebreakSshConnectWith(['commandLinebreak' => 'n']);

    (new Send($connect))->sendStringExec(LINEBREAK_TEST_COMMAND);

    expect($connect->connection->execs)->toBe([LINEBREAK_TEST_COMMAND . "\r"]);
});

test('send string falls back to carriage return for objects without the property', function () {
    $connection = linebreakRecordingConnection();

    (new Send((object) ['connection' => $connection]))->sendString(LINEBREAK_TEST_COMMAND);

    expect($connection->writes)->toBe([LINEBREAK_TEST_COMMAND . "\r"]);
});

test('legacy linebreak never changes the line ending', function (string $linebreak) {
    $connect = linebreakSshConnectWith(['linebreak' => $linebreak]);

    (new Send($connect))->sendString(LINEBREAK_TEST_COMMAND);

    expect($connect->connection->writes)->toBe([LINEBREAK_TEST_COMMAND . "\r"]);
})->with(['r', 'n', 'rn']);
