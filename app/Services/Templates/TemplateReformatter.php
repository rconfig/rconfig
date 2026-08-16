<?php

namespace App\Services\Templates;

use Illuminate\Support\Str;

class TemplateReformatter
{
    private $commentMappings = [
        'main' => [
            'name' => 'Unique name for this template',
            'desc' => 'UI description',
        ],
        'connect' => [
            'timeout' => 'Connection timeout (in seconds)',
            'protocol' => 'Protocol to use: \'telnet\' or \'ssh\'',
            'port' => 'Port number for connection (1–65535)',
        ],
        'auth' => [
            'sshPrivKey' => 'SSH Private Key authentication enabled',
            'username' => 'Prompt string for username',
            'password' => 'Prompt string for password',
            'enable' => 'Enable mode? Set to \'on\' or \'off\'',
            'enableCmd' => 'Command to enter enable mode',
            'enablePassPrmpt' => 'Prompt when enable password is requested',
            'hpAnyKeyStatus' => 'HP-style \'press any key\' prompt active? \'on\' or \'off\'',
            'hpAnyKeyPrmpt' => 'HP-style prompt string (if used)',
        ],
        'config' => [
            'linebreak' => 'Linebreak setting: \'n\' (default) or \'r\'',
            'paging' => 'Set to \'on\' to disable paging',
            'pagingCmd' => 'Command to disable CLI paging',
            'resetPagingCmd' => 'Command to restore CLI paging (optional)',
            'pagerPrompt' => 'DEPRECATED: This value is ignored',
            'pagerPromptCmd' => 'DEPRECATED: This value is ignored',
            'saveConfig' => 'Command to save running config',
            'exitCmd' => 'Command to end session',
        ],
        'options' => [
            'AnsiHost' => 'AnsiHost required for HP and Mikrotik devices - v6 Only',
            'setWindowSize' => 'Terminal window size [columns, rows] - v6 Only',
            'setTerminalDimensions' => 'Terminal dimensions for Ansi sessions [width, height] - v6 Only',
        ],
        'vt100' => [
            'hasSplashScreen' => 'Device shows a splash screen before login? \'on\' or \'off\'',
            'hasSplashScreenEnterKey' => 'Send an enter key to clear the splash screen? \'on\' or \'off\'',
            'splashScreenReadToText' => 'Text on the splash screen to read up to',
            'splashScreenSendControlCode' => 'Control code to send once the splash screen is read',
        ],
    ];

    /** @var array<int, string> */
    private $sectionOrder = ['main', 'connect', 'auth', 'config', 'options', 'vt100'];

    public function reformatTemplateFile(string $inputFile): string
    {
        if (! file_exists($inputFile)) {
            throw new \Exception("Input file does not exist: $inputFile");
        }

        if ($this->isNewFormat($inputFile)) {
            throw new \Exception("Template file is already in the new format: $inputFile");
        }

        $content = file_get_contents($inputFile);
        $reformatted = $this->reformatTemplate($content);

        return $reformatted;
    }

    public function reformatTemplate(string $templateContent): string
    {
        $parsed = $this->parseYamlLike($templateContent);

        return $this->generateReformattedTemplate($parsed);
    }

    /**
     * Determines if a template file is already in the new format
     *
     * @param  string  $filePath  Path to the template file
     * @return bool True if the file is already in the new format, false otherwise
     */
    public function isNewFormat(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            throw new \Exception("Template file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \Exception("Failed to read template file: {$filePath}");
        }

        // Check if the name and desc lines have inline comments
        $hasNameComment = preg_match('/name:\s*["\'].*["\'].*#.*template/i', $content);
        $hasDescComment = preg_match('/desc:\s*["\'].*["\'].*#.*description/i', $content);

        return $hasNameComment && $hasDescComment;
    }

    public function sanitizeFileName($string, $extension = null)
    {
        // if has yml extension, remove it
        if (str_ends_with($string, '.yml')) {
            $string = substr($string, 0, -4);
        }

        // Convert to lowercase and remove accents/special characters
        $string = Str::ascii($string);

        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);

        $string = str_replace('-', '_', $string);

        // Remove any character that's not alphanumeric, underscore, hyphen or dot
        $string = preg_replace('/[^\w\-\.]/', '', $string);

        // Remove multiple consecutive underscores
        $string = preg_replace('/_+/', '_', $string);

        // Trim underscores from beginning and end
        $string = trim($string, '_');

        // If string is empty after sanitization, use a fallback name
        if (empty($string)) {
            $string = 'template_' . Str::random(8);
        }

        // trim and remove trailing crs
        $string = trim($string, "\r\n");
        $string = rtrim($string, "\r\n");

        // Add extension if provided
        if ($extension) {
            $extension = ltrim($extension, '.');
            $string .= '.' . $extension;
        }

        return $string . '.yml';
    }

    /**
     * Splits the template into sections, keeping the raw body of every section so
     * that nothing can be lost. A section whose body is a flat list of key/value
     * pairs also gets a parsed `pairs` array and is rewritten with comments; any
     * other section (nested keys, list items) is carried through verbatim.
     *
     * @return array<string, array{pairs: array<string, string|array<int, string>>|null, lines: array<int, string>}>
     */
    private function parseYamlLike(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $parsed = [];
        $currentSection = null;

        foreach ($lines as $line) {
            $line = rtrim($line);
            $trimmed = trim($line);

            // A section header sits at the start of the line with no value after the colon
            if (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*):$/', $line, $matches)) {
                $currentSection = $matches[1];
                $parsed[$currentSection] = ['pairs' => null, 'lines' => []];

                continue;
            }

            // Anything before the first section header is the old file header, which is regenerated
            if ($currentSection === null) {
                continue;
            }

            // Do not open a section body with blank lines
            if ($trimmed === '' && $parsed[$currentSection]['lines'] === []) {
                continue;
            }

            $parsed[$currentSection]['lines'][] = $line;
        }

        foreach ($parsed as $section => $data) {
            $parsed[$section]['lines'] = $this->trimTrailingBlankLines($data['lines']);
            $parsed[$section]['pairs'] = $this->parseFlatPairs($parsed[$section]['lines']);
        }

        return $parsed;
    }

    /**
     * Parses a section body into key/value pairs, or returns null when the body is
     * not a flat mapping and therefore must be preserved as written.
     *
     * @param  array<int, string>  $lines
     * @return array<string, string|array<int, string>>|null
     */
    private function parseFlatPairs(array $lines): ?array
    {
        $pairs = [];
        $baseIndent = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || $trimmed[0] === '#') {
                continue;
            }

            $indent = strlen($line) - strlen(ltrim($line));

            if ($baseIndent === null) {
                $baseIndent = $indent;
            }

            // Deeper indentation means nested structure this parser cannot represent
            if ($indent !== $baseIndent) {
                return null;
            }

            if (! preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)\s*:\s*(.*)$/', $trimmed, $matches)) {
                return null;
            }

            $key = $matches[1];
            $value = $this->stripInlineComment($matches[2]);

            // Handle array values like [240, 2048]
            if (preg_match('/^\[(.*)\]$/', $value, $arrayMatches)) {
                $pairs[$key] = array_map('trim', explode(',', $arrayMatches[1]));

                continue;
            }

            // Remove quotes and store the raw value
            $pairs[$key] = trim($value, '\'"');
        }

        return $pairs;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    private function trimTrailingBlankLines(array $lines): array
    {
        while ($lines !== [] && trim((string) end($lines)) === '') {
            array_pop($lines);
        }

        return array_values($lines);
    }

    /**
     * @param  array<string, array{pairs: array<string, string|array<int, string>>|null, lines: array<int, string>}>  $parsed
     */
    private function generateReformattedTemplate(array $parsed): string
    {
        $output = [];

        // Add header
        $output[] = '# rConfig connection template – DO NOT EDIT DIRECTLY';
        $output[] = '## Template Notes:';
        $output[] = '## - All free text values must be wrapped in double quotes: " "';
        $output[] = '## - Documentation: https://v8coredocs.rconfig.com/devices/templates/';
        $output[] = '## - Community templates and contributions: https://github.com/rconfig/rConfig-templates';
        $output[] = '';

        // Known sections first, in the documented order
        foreach ($this->sectionOrder as $section) {
            if (! isset($parsed[$section])) {
                continue;
            }

            $output = array_merge($output, $this->renderSection($section, $parsed[$section]));
        }

        // Then anything else the template carried, in the order it was written
        foreach ($parsed as $section => $data) {
            if (in_array($section, $this->sectionOrder, true)) {
                continue;
            }

            $output = array_merge($output, $this->renderSection($section, $data));
        }

        return implode("\n", $output);
    }

    /**
     * @param  array{pairs: array<string, string|array<int, string>>|null, lines: array<int, string>}  $data
     * @return array<int, string>
     */
    private function renderSection(string $section, array $data): array
    {
        $output = ["$section:"];

        if ($data['pairs'] === null) {
            // Not a flat mapping, so keep the section exactly as the author wrote it
            $output = array_merge($output, $data['lines']);
            $output[] = '';

            return $output;
        }

        foreach ($data['pairs'] as $key => $value) {
            $comment = $this->commentMappings[$section][$key] ?? '';
            $formattedValue = $this->formatValue($value);

            if ($comment) {
                $output[] = sprintf('  %-40s # %s', "$key: $formattedValue", $comment);
            } else {
                $output[] = "  $key: $formattedValue";
            }
        }

        $output[] = ''; // Empty line after each section

        return $output;
    }

    /**
     * Strips a trailing inline comment from a raw value while preserving any
     * '#' that appears inside a quoted string.
     *
     * @param  string  $value  The raw value portion captured after the key
     * @return string The value with any inline comment removed
     */
    private function stripInlineComment(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $quote = $value[0];

        if ($quote === '"' || $quote === '\'') {
            $closingPos = strpos($value, $quote, 1);

            if ($closingPos !== false) {
                // Keep the quoted segment and drop anything after the closing quote
                return substr($value, 0, $closingPos + 1);
            }
        }

        // Unquoted value: an inline comment is whitespace followed by '#'
        return trim(preg_replace('/\s+#.*$/s', '', $value));
    }

    private function formatValue($value): string
    {
        if (is_array($value)) {
            return '[' . implode(', ', $value) . ']';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if ($value === 'on' || $value === 'off') {
            return $value;
        }

        if ($value === 'telnet' || $value === 'ssh') {
            return $value;
        }

        if ($value === 'n' || $value === 'r') {
            return "\"$value\"";
        }

        // For everything else, wrap in quotes
        return "\"$value\"";
    }
}
