<?php

namespace App\Services\ConfigCompare;

use App\Models\Setting;
use App\Services\Templates\CompareExclusionTemplateService;
use File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ConfigCompareExclusionCleaner
{
    protected string $file;
    protected string $command;
    protected ?string $exclusionFile;

    /** @var array<string, mixed> */
    protected array $compareSettings;

    public function __construct(string $file, string $command = '')
    {
        if (! file_exists($file)) {
            throw new InvalidArgumentException("File does not exist: {$file}");
        }

        $this->file = $file;
        $this->command = $command;
        $settings = Setting::where('id', 1)->first();
        $this->exclusionFile = $settings?->config_compare_exclusion_file;
        $this->compareSettings = $settings?->config_compare_settings ?? [];
    }

    /**
     * Apply the exclusion rules to the config file and return the path to the
     * cleaned file (a temp file when content changed, otherwise the original).
     */
    public function excludeLines(): string
    {
        if (empty($this->exclusionFile)) {
            Log::info('ConfigCompareExclusionCleaner: Installing default template');
            (new CompareExclusionTemplateService)->installDefaultTemplate();

            $this->exclusionFile = Setting::where('id', 1)->value('config_compare_exclusion_file');
        }

        $exclusionArr = $this->parseExclusionFile();
        $patternsCombined = $this->mergeGlobalAndCommandRules($exclusionArr, $this->command);

        $originalContent = (string) file_get_contents($this->file);

        if (! empty($patternsCombined)) {
            $cleanedText = $this->excludePatterns($originalContent, $patternsCombined);
        } else {
            $cleanedText = $originalContent;
        }

        $cleanedText = $this->normalizeWhitespace($cleanedText);

        if ($cleanedText !== $originalContent) {
            $tempFileName = tmp_dir() . 'temp_' . uniqid() . '.txt';
            File::put($tempFileName, $cleanedText);

            return $tempFileName;
        }

        return $this->file;
    }

    /**
     * @return array<int, array{description?: string, command?: string, rules: array<int, string>}>
     */
    public function parseExclusionFile(): array
    {
        return (new ConfigCompareExclusionPolicyParser)->parse($this->exclusionFile);
    }

    /**
     * @param  array<int, array{description?: string, command?: string, rules: array<int, string>}>  $exclusionArray
     * @return array<int, string>
     */
    public function mergeGlobalAndCommandRules(array $exclusionArray, string $searchKeyword): array
    {
        $searchKeyword = trim($searchKeyword);
        $globalRules = [];
        $commandRules = [];

        foreach ($exclusionArray as $block) {
            $command = trim($block['command'] ?? '');
            if (isset($block['command'])) {
                if ($command === 'global') {
                    $globalRules = $block['rules'];
                } elseif ($command === $searchKeyword) {
                    $commandRules = $block['rules'];
                }
            }
        }

        return array_merge($globalRules, $commandRules);
    }

    /**
     * @param  array<int, string>  $patterns
     */
    public function excludePatterns(string $text, array $patterns): string
    {
        foreach ($patterns as $pattern) {
            try {
                if (@preg_match($pattern, '') === false) {
                    Log::warning("ConfigCompareExclusionCleaner: Invalid regex pattern: {$pattern}");

                    continue;
                }

                if ($this->isMultilinePattern($pattern)) {
                    $text = $this->processMultilinePattern($text, $pattern);
                } else {
                    $text = $this->processSingleLinePattern($text, $pattern);
                }
            } catch (\Throwable $e) {
                Log::error("ConfigCompareExclusionCleaner: Error processing pattern {$pattern}: " . $e->getMessage());
            }
        }

        return $text;
    }

    protected function isMultilinePattern(string $pattern): bool
    {
        $modifiers = $this->extractRegexModifiers($pattern);

        return str_contains($modifiers, 's') || str_contains($modifiers, 'm');
    }

    protected function processMultilinePattern(string $text, string $pattern): string
    {
        $cleanPattern = $this->validateAndNormalizeRegexPattern($pattern);

        $result = @preg_replace($cleanPattern, '', $text);

        if ($result === null) {
            $error = preg_last_error();
            Log::warning("ConfigCompareExclusionCleaner: preg_replace failed with error code: {$error} for pattern: {$cleanPattern}");

            return $text;
        }

        return $result;
    }

    protected function processSingleLinePattern(string $text, string $pattern): string
    {
        $lines = explode(PHP_EOL, $text);

        $lines = array_filter($lines, function ($line) use ($pattern) {
            return @preg_match($pattern, $line) !== 1;
        });

        return implode(PHP_EOL, $lines);
    }

    protected function extractRegexModifiers(string $pattern): string
    {
        if (empty($pattern)) {
            return '';
        }

        $delimiter = $pattern[0];
        $lastDelimiterPos = strrpos($pattern, $delimiter);

        if ($lastDelimiterPos === false || $lastDelimiterPos === 0) {
            return '';
        }

        return substr($pattern, $lastDelimiterPos + 1);
    }

    protected function validateAndNormalizeRegexPattern(string $pattern): string
    {
        $validDelimiters = ['/', '#', '~', '!', '@', '%', '`', '|'];

        if (empty($pattern)) {
            throw new InvalidArgumentException('Empty regex pattern provided');
        }

        $firstChar = $pattern[0];

        if (! in_array($firstChar, $validDelimiters)) {
            return '/' . $this->escapeForwardSlashesInPattern($pattern) . '/';
        }

        $delimiter = $firstChar;
        $lastDelimiterPos = strrpos($pattern, $delimiter);

        if ($lastDelimiterPos !== false && $lastDelimiterPos > 0) {
            $regexBody = substr($pattern, 0, $lastDelimiterPos + 1);
            $modifiers = substr($pattern, $lastDelimiterPos + 1);

            // Remove 'g' flag as PHP does not support it
            $modifiers = str_replace('g', '', $modifiers);

            return $regexBody . $modifiers;
        }

        return $pattern;
    }

    protected function escapeForwardSlashesInPattern(string $pattern): string
    {
        return str_replace('/', '\/', $pattern);
    }

    public function cleanTempFiles(): void
    {
        $files = File::glob(tmp_dir() . '/*.txt');
        File::delete($files);
    }

    private function normalizeWhitespace(string $text): string
    {
        $lines = explode("\n", $text);

        if ($this->shouldIgnoreWhitespace()) {
            $result = [];
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $result[] = rtrim($line);
                }
            }

            return implode("\n", $result);
        }

        return implode("\n", array_map('rtrim', $lines));
    }

    private function shouldIgnoreWhitespace(): bool
    {
        if (empty($this->compareSettings)) {
            return false;
        }

        return $this->compareSettings['ignoreWhitespace'] ?? false;
    }
}
