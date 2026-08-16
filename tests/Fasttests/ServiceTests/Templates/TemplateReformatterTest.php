<?php

namespace Tests\Fasttests\ServiceTests\Templates;

use App\Services\Templates\TemplateReformatter;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class TemplateReformatterTest extends TestCase
{
    protected string $oldFormatPath;
    protected string $newFormatPath;
    protected string $inlineCommentsPath;
    protected string $vt100Path;
    protected TemplateReformatter $reformatter;

    public function setUp(): void
    {
        parent::setUp();

        $this->oldFormatPath = base_path('tests/storage/templates/oldformat.yml');
        $this->newFormatPath = base_path('tests/storage/templates/newformat.yml');
        $this->inlineCommentsPath = base_path('tests/storage/templates/inlinecomments.yml');
        $this->vt100Path = base_path('tests/storage/templates/vt100.yml');

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

    /**
     * Regression for RCO-1300: reformatting must not drop the vt100 section, which
     * drives splash screen login on RuggedCom and Avaya style devices.
     */
    public function test_vt100_section_survives_a_reformat(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->vt100Path);

        $parsed = Yaml::parse($result);

        $this->assertArrayHasKey('vt100', $parsed);
        $this->assertSame('on', $parsed['vt100']['hasSplashScreen']);
        $this->assertSame('off', $parsed['vt100']['hasSplashScreenEnterKey']);
        $this->assertSame('Ctrl-Y', $parsed['vt100']['splashScreenReadToText']);
        $this->assertSame('Y', $parsed['vt100']['splashScreenSendControlCode']);

        // The vt100 keys must not leak into the preceding section
        $this->assertArrayNotHasKey('vt100', $parsed['options']);
        $this->assertArrayNotHasKey('hasSplashScreen', $parsed['options']);

        // And the section is documented like every other known section
        $this->assertStringContainsString('# Device shows a splash screen before login?', $result);
    }

    /**
     * Regression for RCO-1300: every section present in the input must be present
     * in the output, including sections the reformatter knows nothing about.
     */
    public function test_no_section_is_lost_during_a_reformat(): void
    {
        $before = Yaml::parse(file_get_contents($this->vt100Path));
        $after = Yaml::parse($this->reformatter->reformatTemplateFile($this->vt100Path));

        $this->assertSame(array_keys($before), array_keys($after));
    }

    /**
     * Sections the reformatter cannot represent as a flat mapping, such as the
     * nested lists in failure_criteria, are carried through unchanged.
     */
    public function test_nested_sections_are_preserved_verbatim(): void
    {
        $before = Yaml::parse(file_get_contents($this->vt100Path));
        $result = $this->reformatter->reformatTemplateFile($this->vt100Path);

        $after = Yaml::parse($result);

        $this->assertSame($before['failure_criteria'], $after['failure_criteria']);
        $this->assertSame([1, 2, 255], $after['failure_criteria']['exit_codes']);
        $this->assertSame(
            ['Connection refused', 'Authentication failed'],
            $after['failure_criteria']['error_patterns']
        );
    }

    /**
     * Regression for RCO-1299: linebreak and hpAnyKeyPrmpt are read by nothing, so a
     * reformat must label them the way pagerPrompt is already labelled rather than
     * describing them as working settings.
     */
    public function test_dead_template_keys_are_documented_as_deprecated(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

        foreach (['linebreak', 'hpAnyKeyPrmpt', 'pagerPrompt', 'pagerPromptCmd'] as $key) {
            $this->assertMatchesRegularExpression(
                '/^\s{2}' . $key . ':.*# DEPRECATED: This value is ignored$/m',
                $result,
                "{$key} must be documented as deprecated."
            );
        }

        $this->assertStringNotContainsString('Linebreak setting', $result);
        $this->assertStringNotContainsString('HP-style prompt string', $result);
    }

    /**
     * Deprecated keys are documented, not deleted, so a template carrying them
     * survives a reformat untouched.
     */
    public function test_deprecated_keys_keep_their_values_through_a_reformat(): void
    {
        $after = Yaml::parse($this->reformatter->reformatTemplateFile($this->oldFormatPath));

        $this->assertSame('n', $after['config']['linebreak']);
        $this->assertSame('--More--', $after['config']['pagerPrompt']);
        $this->assertSame('Press any key to continue', $after['auth']['hpAnyKeyPrmpt']);
    }

    /**
     * setTerminalDimensions only ever drove ANSI output rendering, never the
     * negotiated terminal, so the comment must not claim otherwise.
     */
    public function test_terminal_dimensions_are_documented_as_ansi_only(): void
    {
        $result = $this->reformatter->reformatTemplateFile($this->oldFormatPath);

        $this->assertMatchesRegularExpression(
            '/^\s{2}setTerminalDimensions:.*# .*ANSI.*$/m',
            $result
        );
        $this->assertStringNotContainsString('Terminal dimensions for Ansi sessions', $result);
    }

    /**
     * A reformat of an already reformatted template must be a no-op beyond
     * whitespace, so repeated clicks of the button cannot erode a template.
     */
    public function test_reformatting_is_stable_across_repeated_runs(): void
    {
        $once = $this->reformatter->reformatTemplate(file_get_contents($this->vt100Path));
        $twice = $this->reformatter->reformatTemplate($once);

        $this->assertSame(Yaml::parse($once), Yaml::parse($twice));
    }
}
