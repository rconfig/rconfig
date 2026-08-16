<?php

namespace Tests\Unit\Connections;

use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The telnet read loop spots the device prompt with a regex delimited by `/`, and the
 * prompt itself is free text from the device record. A prompt carrying the delimiter
 * closed the pattern early, so preg_match() failed on every character and the loop ran
 * until the socket died instead of matching.
 *
 * Read holds a live socket, so it is built without its constructor and driven through
 * its prompt handling alone.
 */
class TelnetPromptPatternTest extends TestCase
{
    private function buildPattern(string $prompt): string
    {
        $reflection = new ReflectionClass(TelnetRead::class);
        $read = $reflection->newInstanceWithoutConstructor();

        return $reflection->getMethod('buildPromptPattern')->invoke($read, $prompt);
    }

    private function matchesPrompt(string $prompt, string $buffer): bool
    {
        $reflection = new ReflectionClass(TelnetRead::class);
        $read = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('prompt')->setValue($read, $prompt);
        $reflection->getProperty('promptPattern')->setValue($read, $this->buildPattern($prompt));
        $reflection->getProperty('data')->setValue($read, $buffer);

        return (bool) $reflection->getMethod('readToPrompt')->invoke($read);
    }

    public function test_prompt_containing_the_delimiter_still_matches(): void
    {
        $this->assertTrue($this->matchesPrompt('admin@sw1/config#', "show run\r\nadmin@sw1/config#"));
    }

    public function test_mikrotik_style_prompt_still_matches(): void
    {
        $prompt = '[admin@MikroTik] /interface>';

        $this->assertTrue($this->matchesPrompt($prompt, "output\r\n[admin@MikroTik] /interface>"));
    }

    public function test_plain_prompt_still_matches(): void
    {
        $this->assertTrue($this->matchesPrompt('sw1#', "show run\r\nsw1#"));
    }

    public function test_prompt_must_appear_at_the_end_of_the_buffer(): void
    {
        $this->assertFalse($this->matchesPrompt('sw1#', "sw1#\r\nshow running-config\r\n"));
    }

    public function test_buffer_without_the_prompt_does_not_match(): void
    {
        $this->assertFalse($this->matchesPrompt('admin@sw1/config#', "show run\r\nsomething else"));
    }

    public function test_prompt_full_of_metacharacters_matches_as_typed(): void
    {
        $this->assertTrue($this->matchesPrompt('sw1(config)#', "show run\r\nsw1(config)#"));
        $this->assertTrue($this->matchesPrompt('R1.core+#', "show run\r\nR1.core+#"));
        $this->assertFalse($this->matchesPrompt('sw1(config)#', "show run\r\nsw2(config)#"));
    }

    public function test_empty_prompt_still_matches_anything(): void
    {
        $this->assertTrue($this->matchesPrompt('', "show run\r\n"));
    }

    public function test_regex_capable_prompts_are_preserved(): void
    {
        $this->assertTrue($this->matchesPrompt('sw1[>#]', "show run\r\nsw1#"));
        $this->assertTrue($this->matchesPrompt('sw1[>#]', "show run\r\nsw1>"));
        $this->assertFalse($this->matchesPrompt('sw1[>#]', "show run\r\nsw1$"));
    }

    public function test_prompt_that_cannot_compile_falls_back_to_a_literal_match(): void
    {
        $prompt = 'sw1(config';

        $this->assertSame('/' . preg_quote($prompt, '/') . '$/', $this->buildPattern($prompt));
        $this->assertTrue($this->matchesPrompt($prompt, "show run\r\nsw1(config"));
    }

    public function test_every_built_pattern_compiles(): void
    {
        $prompts = [
            'sw1#',
            'admin@sw1/config#',
            '[admin@MikroTik] /interface>',
            'sw1(config',
            'sw1[>#',
            'sw1*',
            '\\',
        ];

        foreach ($prompts as $prompt) {
            $this->assertNotFalse(
                preg_match($this->buildPattern($prompt), ''),
                'Pattern built for prompt [' . $prompt . '] does not compile.'
            );
        }
    }
}
