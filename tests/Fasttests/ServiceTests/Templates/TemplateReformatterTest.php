<?php

namespace Tests\Fasttests\ServiceTests\Templates;

use App\Services\Templates\TemplateReformatter;
use Tests\TestCase;

class TemplateReformatterTest extends TestCase
{
    protected string $oldFormatPath;
    protected string $newFormatPath;
    protected string $inlineCommentsPath;
    protected TemplateReformatter $reformatter;

    public function setUp(): void
    {
        parent::setUp();

        $this->oldFormatPath = base_path('tests/storage/templates/oldformat.yml');
        $this->newFormatPath = base_path('tests/storage/templates/newformat.yml');
        $this->inlineCommentsPath = base_path('tests/storage/templates/inlinecomments.yml');

        $this->reformatter = new TemplateReformatter;
    }

    public function test_can_instantiate_template_reformatter(): void
    {
        $this->assertInstanceOf(TemplateReformatter::class, $this->reformatter);
    }

    public function test_detects_already_new_format_template(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template file is already in the new format: ' . $this->newFormatPath);

        $this->reformatter->reformatTemplateFile($this->newFormatPath);
    }

    public function test_can_determine_template_format_correctly(): void
    {
        $this->assertFalse($this->reformatter->isNewFormat($this->oldFormatPath));
        $this->assertTrue($this->reformatter->isNewFormat($this->newFormatPath));
    }

    public function test_can_convert_old_format_to_new_format(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_converted_template_has_correct_structure(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

        $this->assertStringContainsString('name: "SSH Private Key Template"', $result);
        $this->assertStringContainsString('# Unique name for this template', $result);
        $this->assertStringContainsString('# Port number for connection', $result);
        $this->assertStringContainsString('exitCmd: "quit"', $result);
        $this->assertStringContainsString('setTerminalDimensions: [260, 100000]', $result);
    }

    public function test_handles_invalid_file_path(): void
    {
        $this->expectException(\Exception::class);

        $this->reformatter->reformatTemplateFile(base_path('tests/storage/templates/does-not-exist.yml'));
    }

    /**
     * Regression for issue #303: an input that already carries inline comments
     * must not produce values with unclosed quotes.
     */
    public function test_reformatting_template_with_inline_comments_does_not_break_quotes(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->inlineCommentsPath);

        // The original inline comment text must not survive inside the value
        $this->assertStringNotContainsString('# Cisco IOS via TELNET without enable mode"', $result);
        $this->assertStringNotContainsString('# Disable CLI paging"', $result);

        // The name value is cleanly quoted and the rConfig comment sits outside it
        $this->assertStringContainsString('name: "Cisco IOS - TELNET - No Enable - test 2500"', $result);
        $this->assertStringContainsString('# Unique name for this template', $result);

        // Every value line must have a balanced number of double quotes (0 or 2)
        foreach (explode("\n", $result) as $line) {
            if (! preg_match('/^\s{2}[a-zA-Z]/', $line)) {
                continue;
            }

            $this->assertSame(
                0,
                substr_count($line, '"') % 2,
                "Line has unbalanced quotes: {$line}"
            );
        }
    }

    public function test_unquoted_and_array_values_drop_their_inline_comments(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->inlineCommentsPath);

        // Unquoted scalars keep their bare value with the original comment stripped
        $this->assertStringContainsString('protocol: telnet ', $result);
        $this->assertStringContainsString('port: 23 ', $result);
        $this->assertStringNotContainsString('protocol: "telnet', $result);

        // Array value is preserved without the trailing inline comment
        $this->assertStringContainsString('setWindowSize: [240, 2048]', $result);
        $this->assertStringNotContainsString('setWindowSize: "[240, 2048]', $result);
    }

    public function test_hash_inside_quoted_value_is_preserved(): void
    {
        $template = "main:\n  name: \"Cisco #1 Core\"   # inline note\n  desc: \"edge\"\n";

        $result = $this->reformatter->reformatTemplate($template);

        $this->assertStringContainsString('name: "Cisco #1 Core"', $result);
        $this->assertStringNotContainsString('inline note', $result);
    }
}
