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
    ];
    private $sectionOrder = ['main', 'connect', 'auth', 'config', 'options'];

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

    private function parseYamlLike(string $content): array
    {
        $lines = explode("\n", $content);
        $parsed = [];
        $currentSection = null;
        $inComment = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip comments and empty lines during parsing
            if (empty($trimmed) || $trimmed[0] === '#') {
                continue;
            }

            // Check if this is a section header
            if (preg_match('/^([a-zA-Z]+):$/', $trimmed, $matches)) {
                $currentSection = $matches[1];
                $parsed[$currentSection] = [];

                continue;
            }

            // Parse key-value pairs
            if ($currentSection && preg_match('/^([a-zA-Z][a-zA-Z0-9]*)\s*:\s*(.*)$/', $trimmed, $matches)) {
                $key = $matches[1];
                $value = $matches[2];

                // Handle array values like [240, 2048]
                if (preg_match('/^\[(.*)\]$/', $value, $arrayMatches)) {
                    $arrayValues = array_map('trim', explode(',', $arrayMatches[1]));
                    $parsed[$currentSection][$key] = $arrayValues;
                } else {
                    // Remove quotes and store the raw value
                    $value = trim($value, '\'"');
                    $parsed[$currentSection][$key] = $value;
                }
            }
        }

        return $parsed;
    }

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

        // Process sections in order
        foreach ($this->sectionOrder as $section) {
            if (! isset($parsed[$section])) {
                continue;
            }

            $output[] = "$section:";

            foreach ($parsed[$section] as $key => $value) {
                $comment = $this->commentMappings[$section][$key] ?? '';
                $formattedValue = $this->formatValue($value);

                if ($comment) {
                    $line = sprintf('  %-40s # %s', "$key: $formattedValue", $comment);
                } else {
                    $line = "  $key: $formattedValue";
                }

                $output[] = $line;
            }

            $output[] = ''; // Empty line after each section
        }

        return implode("\n", $output);
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
