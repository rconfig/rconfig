<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\Telnet\Connect as TelnetConnect;
use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;
use PHPUnit\Framework\TestCase;

/**
 * config.linebreak, config.pagerPrompt, config.pagerPromptCmd and auth.hpAnyKeyPrmpt
 * were read off every template into properties nothing ever consumed. The reads were
 * unguarded, so a template that left a key out raised an undefined array key warning
 * for a value that was then discarded.
 *
 * All four are deprecated and no longer read. Templates carrying them still load,
 * templates without them no longer warn.
 */
class DeprecatedTemplateKeysTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private const DEPRECATED_KEYS = ['linebreak', 'pagerPrompt', 'pagerPromptCmd', 'hpAnyKeyPrmpt'];

    /**
     * @param  callable(): void  $callback
     * @return array<int, string>
     */
    private function phpErrorsWhile(callable $callback): array
    {
        $errors = [];

        set_error_handler(function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = $errstr;

            return true;
        }, E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $errors;
    }

    public function test_ssh_connect_no_longer_declares_the_deprecated_properties(): void
    {
        foreach (self::DEPRECATED_KEYS as $key) {
            $this->assertFalse(
                property_exists(SshConnect::class, $key),
                "SSH Connect still declares the deprecated {$key} property."
            );
        }
    }

    public function test_telnet_connect_no_longer_declares_the_deprecated_properties(): void
    {
        foreach (self::DEPRECATED_KEYS as $key) {
            $this->assertFalse(
                property_exists(TelnetConnect::class, $key),
                "Telnet Connect still declares the deprecated {$key} property."
            );
        }
    }

    public function test_telnet_read_no_longer_declares_the_deprecated_pager_prompt(): void
    {
        $this->assertFalse(property_exists(TelnetRead::class, 'pagerPrompt'));
    }

    public function test_ssh_connect_builds_without_warnings_when_a_template_omits_the_deprecated_keys(): void
    {
        $params = DeviceParamsBuilder::forSsh([
            'config' => ['linebreak' => null, 'pagerPrompt' => null, 'pagerPromptCmd' => null],
            'auth' => ['hpAnyKeyPrmpt' => null],
        ]);

        $errors = $this->phpErrorsWhile(function () use ($params): void {
            new SshConnect($params, false);
        });

        $this->assertSame([], $errors);
    }

    public function test_telnet_connect_builds_without_warnings_when_a_template_omits_the_deprecated_keys(): void
    {
        $params = DeviceParamsBuilder::forTelnet([
            'config' => ['linebreak' => null, 'pagerPrompt' => null, 'pagerPromptCmd' => null],
            'auth' => ['hpAnyKeyPrmpt' => null],
        ]);

        $errors = $this->phpErrorsWhile(function () use ($params): void {
            new TelnetConnect($params, false);
        });

        $this->assertSame([], $errors);
    }

    public function test_ssh_connect_builds_without_warnings_when_a_template_still_carries_the_deprecated_keys(): void
    {
        $params = DeviceParamsBuilder::forSsh([
            'config' => ['linebreak' => 'n', 'pagerPrompt' => '--More--', 'pagerPromptCmd' => ' '],
            'auth' => ['hpAnyKeyPrmpt' => 'Press any key to continue'],
        ]);

        $errors = $this->phpErrorsWhile(function () use ($params): void {
            new SshConnect($params, false);
        });

        $this->assertSame([], $errors);
    }

    public function test_telnet_connect_builds_without_warnings_when_a_template_still_carries_the_deprecated_keys(): void
    {
        $params = DeviceParamsBuilder::forTelnet([
            'config' => ['linebreak' => 'n', 'pagerPrompt' => '--More--', 'pagerPromptCmd' => ' '],
            'auth' => ['hpAnyKeyPrmpt' => 'Press any key to continue'],
        ]);

        $errors = $this->phpErrorsWhile(function () use ($params): void {
            new TelnetConnect($params, false);
        });

        $this->assertSame([], $errors);
    }
}
