<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;
use phpseclib3\File\ANSI;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The template's setTerminalDimensions used to be written onto the phpseclib SSH2
 * object as a dynamic property and read back off it later. Creating a dynamic
 * property on a class that does not allow them is deprecated from PHP 8.2 and will
 * be removed, at which point the value would silently read back as unset. The value
 * belongs on the connection object this application owns.
 *
 * It only ever fed ANSI rendering, never the negotiated PTY, so these tests pin the
 * effect it actually has.
 */
class TerminalDimensionsTest extends TestCase
{
    /**
     * @return array{0: int, 1: int} the ANSI object's max x and max y
     */
    private function ansiDimensions(ANSI $ansi): array
    {
        $reflection = new ReflectionClass($ansi);

        return [
            $reflection->getProperty('max_x')->getValue($ansi),
            $reflection->getProperty('max_y')->getValue($ansi),
        ];
    }

    private function ansiForConnectionObject(object $connectionObj): ANSI
    {
        $reflection = new ReflectionClass(SshSendCommand::class);
        $sendCommand = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('connectionObj')->setValue($sendCommand, $connectionObj);

        return $reflection->getMethod('ansiForSession')->invoke($sendCommand);
    }

    public function test_connect_does_not_bolt_a_dynamic_property_onto_the_ssh_object(): void
    {
        $connect = new SshConnect(
            DeviceParamsBuilder::forSsh(['options' => ['setTerminalDimensions' => [132, 50]]]),
            false
        );

        $connection = $connect->connect();

        $this->assertFalse(
            property_exists($connection, 'setTerminalDimensions'),
            'setTerminalDimensions must not be set as a dynamic property on the phpseclib SSH2 object.'
        );
    }

    public function test_connect_keeps_the_template_dimensions_on_the_connection_object(): void
    {
        $connect = new SshConnect(
            DeviceParamsBuilder::forSsh(['options' => ['setTerminalDimensions' => [132, 50]]]),
            false
        );

        $connect->connect();

        $this->assertSame([132, 50], $connect->setTerminalDimensions);
    }

    public function test_ansi_session_uses_the_template_dimensions(): void
    {
        $ansi = $this->ansiForConnectionObject((object) ['setTerminalDimensions' => [132, 50]]);

        $this->assertSame([131, 49], $this->ansiDimensions($ansi));
    }

    public function test_ansi_session_uses_the_template_dimensions_given_as_strings(): void
    {
        $ansi = $this->ansiForConnectionObject((object) ['setTerminalDimensions' => ['132', '50']]);

        $this->assertSame([131, 49], $this->ansiDimensions($ansi));
    }

    public function test_ansi_session_falls_back_to_the_default_dimensions_when_the_template_omits_them(): void
    {
        $ansi = $this->ansiForConnectionObject((object) ['setTerminalDimensions' => null]);

        $this->assertSame([79, 23], $this->ansiDimensions($ansi));
    }

    public function test_ansi_session_ignores_a_malformed_dimensions_value(): void
    {
        $ansi = $this->ansiForConnectionObject((object) ['setTerminalDimensions' => [132]]);

        $this->assertSame([79, 23], $this->ansiDimensions($ansi));
    }
}
