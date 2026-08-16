<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\Params\TemplateNormaliser;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\Yaml\Yaml;

/**
 * Template switches used to be consumed exactly as the YAML parser typed them, and the
 * parser types `on` / `off` / `yes` / `no` as strings but `true` / `false` as booleans.
 * Consumers then compared the raw value each in their own way, so the same authoring
 * mistake activated enable mode and silently failed to disable paging. Everything is
 * settled on 'on' / 'off' here instead.
 */
class TemplateNormaliserTest extends TestCase
{
    private TemplateNormaliser $normaliser;

    /**
     * @var list<array{level: mixed, message: string|\Stringable, context: array<string, mixed>}>
     */
    private array $logged = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->logged = [];

        $logger = new class($this->logged) extends AbstractLogger
        {
            /**
             * @param  list<array{level: mixed, message: string|\Stringable, context: array<string, mixed>}>  $logged
             */
            public function __construct(private array &$logged) {}

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->logged[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };

        $this->normaliser = new TemplateNormaliser($logger);
    }

    /**
     * @return list<array{level: mixed, message: string|\Stringable, context: array<string, mixed>}>
     */
    private function loggedAt(string $level): array
    {
        return array_values(array_filter($this->logged, fn (array $entry): bool => $entry['level'] === $level));
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    private function normalise(array $template): array
    {
        return $this->normaliser->normalise($template) ?? [];
    }

    public function test_yaml_types_on_and_true_differently_which_is_the_whole_problem(): void
    {
        $parsed = Yaml::parse("paging: on\nenable: true\nsplash: yes\nquiet: off\n");

        $this->assertSame('on', $parsed['paging']);
        $this->assertTrue($parsed['enable']);
        $this->assertSame('yes', $parsed['splash']);
        $this->assertSame('off', $parsed['quiet']);
    }

    public function test_boolean_true_and_false_become_on_and_off(): void
    {
        $result = $this->normalise([
            'auth' => ['enable' => true],
            'config' => ['paging' => false],
        ]);

        $this->assertSame('on', $result['auth']['enable']);
        $this->assertSame('off', $result['config']['paging']);
    }

    public function test_yes_and_no_become_on_and_off(): void
    {
        $result = $this->normalise([
            'options' => ['AnsiHost' => 'yes'],
            'config' => ['isMikrotik' => 'no'],
        ]);

        $this->assertSame('on', $result['options']['AnsiHost']);
        $this->assertSame('off', $result['config']['isMikrotik']);
    }

    public function test_canonical_values_are_left_alone(): void
    {
        $result = $this->normalise([
            'auth' => ['enable' => 'on', 'hpAnyKeyStatus' => 'off'],
        ]);

        $this->assertSame('on', $result['auth']['enable']);
        $this->assertSame('off', $result['auth']['hpAnyKeyStatus']);
    }

    public function test_casing_and_surrounding_whitespace_are_tolerated(): void
    {
        $result = $this->normalise([
            'auth' => ['enable' => ' ON ', 'sshInteractive' => 'TRUE'],
            'vt100' => ['hasSplashScreen' => 'No'],
        ]);

        $this->assertSame('on', $result['auth']['enable']);
        $this->assertSame('on', $result['auth']['sshInteractive']);
        $this->assertSame('off', $result['vt100']['hasSplashScreen']);
    }

    public function test_an_empty_value_reads_as_off(): void
    {
        $result = $this->normalise(['config' => ['paging' => '']]);

        $this->assertSame('off', $result['config']['paging']);
    }

    public function test_a_null_value_stays_null(): void
    {
        $result = $this->normalise(['config' => ['paging' => null]]);

        $this->assertNull($result['config']['paging']);
    }

    public function test_absent_keys_and_absent_sections_stay_absent(): void
    {
        $result = $this->normalise([
            'auth' => ['enable' => 'on'],
        ]);

        $this->assertArrayNotHasKey('paging', $result['auth']);
        $this->assertArrayNotHasKey('config', $result);
        $this->assertArrayNotHasKey('vt100', $result);
    }

    public function test_keys_that_are_not_switches_are_untouched(): void
    {
        $result = $this->normalise([
            'config' => ['paging' => 'yes', 'pagingCmd' => 'terminal length 0', 'exitCmd' => 'no paging'],
            'auth' => ['username' => 'Username:', 'enable' => 'off'],
        ]);

        $this->assertSame('terminal length 0', $result['config']['pagingCmd']);
        $this->assertSame('no paging', $result['config']['exitCmd']);
        $this->assertSame('Username:', $result['auth']['username']);
    }

    public function test_an_unrecognised_switch_is_left_as_written_and_logged(): void
    {
        $result = $this->normalise(['config' => ['paging' => 'sometimes']]);

        $this->assertSame('sometimes', $result['config']['paging']);

        $warnings = $this->loggedAt('warning');
        $this->assertCount(1, $warnings);
        $this->assertSame('config.paging', $warnings[0]['context']['key']);
        $this->assertSame('sometimes', $warnings[0]['context']['value']);
    }

    public function test_a_value_that_had_to_be_rewritten_is_logged(): void
    {
        $this->normalise([
            'connect' => ['protocol' => 'SSH'],
            'auth' => ['enable' => true],
            'config' => ['paging' => 'off'],
        ]);

        $keys = array_column(array_column($this->loggedAt('info'), 'context'), 'key');

        $this->assertContains('connect.protocol', $keys);
        $this->assertContains('auth.enable', $keys);
        $this->assertNotContains('config.paging', $keys, 'A value already in canonical form should not be logged.');
    }

    public function test_protocol_is_lowercased_and_trimmed(): void
    {
        $result = $this->normalise(['connect' => ['protocol' => ' SSH ']]);

        $this->assertSame('ssh', $result['connect']['protocol']);
    }

    public function test_protocol_already_lowercase_is_left_alone(): void
    {
        $result = $this->normalise(['connect' => ['protocol' => 'telnet']]);

        $this->assertSame('telnet', $result['connect']['protocol']);
    }

    public function test_a_null_template_is_returned_untouched(): void
    {
        $this->assertNull($this->normaliser->normalise(null));
    }

    public function test_a_full_template_normalises_every_switch_it_carries(): void
    {
        $template = Yaml::parse(file_get_contents(__DIR__ . '/../../storage/templates/vt100.yml'));

        $result = $this->normalise($template);

        $this->assertSame('ssh', $result['connect']['protocol']);
        $this->assertSame('off', $result['auth']['enable']);
        $this->assertSame('off', $result['config']['paging']);
        $this->assertSame('on', $result['options']['AnsiHost']);
        $this->assertSame('on', $result['vt100']['hasSplashScreen']);
        $this->assertSame('off', $result['vt100']['hasSplashScreenEnterKey']);
        $this->assertSame('terminal length 0', $result['config']['pagingCmd']);
    }
}
