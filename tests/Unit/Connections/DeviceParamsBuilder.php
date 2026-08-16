<?php

namespace Tests\Unit\Connections;

/**
 * Builds the device params object the connection classes are constructed from, in
 * the shape ConnectionParams hands over: one array per template section plus the
 * device record. Only the keys the connection classes actually read are included,
 * so a test can drop a section key to prove the constructor tolerates its absence.
 */
class DeviceParamsBuilder
{
    /**
     * @param  array<string, array<string, mixed>>  $overrides  merged per section, a null value removes the key
     */
    public static function forSsh(array $overrides = []): object
    {
        return self::build('ssh', 22, $overrides);
    }

    /**
     * @param  array<string, array<string, mixed>>  $overrides  merged per section, a null value removes the key
     */
    public static function forTelnet(array $overrides = []): object
    {
        return self::build('telnet', 23, $overrides);
    }

    /**
     * @param  array<string, array<string, mixed>>  $overrides
     */
    private static function build(string $protocol, int $port, array $overrides): object
    {
        $sections = [
            'main' => [
                'name' => 'Test Template',
                'desc' => 'Test Template',
            ],
            'connect' => [
                'timeout' => 10,
                'protocol' => $protocol,
                'port' => $port,
            ],
            'auth' => [
                'username' => 'Username:',
                'password' => 'Password:',
                'enable' => 'off',
                'enableCmd' => 'enable',
                'enablePassPrmpt' => 'Password:',
                'hpAnyKeyStatus' => 'off',
            ],
            'config' => [
                'paging' => 'on',
                'pagingCmd' => 'terminal length 0',
                'resetPagingCmd' => 'terminal length 24',
                'saveConfig' => 'write mem',
                'exitCmd' => 'exit',
            ],
            'options' => [],
            'vt100' => [],
            'deviceparams' => [
                'id' => 1,
                'device_ip' => '127.0.0.1',
                'device_username' => 'rconfig',
                'device_password' => 'rconfig',
                'device_enable_password' => 'rconfig',
                'device_main_prompt' => 'router1#',
                'device_enable_prompt' => 'router1#',
                'device_cred_id' => 0,
                'ssh_key_id' => null,
                'commands' => ['show run'],
            ],
        ];

        foreach ($overrides as $section => $values) {
            foreach ($values as $key => $value) {
                if ($value === null) {
                    unset($sections[$section][$key]);

                    continue;
                }

                $sections[$section][$key] = $value;
            }
        }

        return (object) $sections;
    }
}
