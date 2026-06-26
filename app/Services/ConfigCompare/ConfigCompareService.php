<?php

namespace App\Services\ConfigCompare;

use App\Models\Setting;
use Jfcherng\Diff\DiffHelper;
use Jfcherng\Diff\Renderer\RendererConstant;

class ConfigCompareService
{
    // HTML renderers: Combined, Inline, JsonHtml, SideBySide
    protected string $rendererName = 'Inline';

    /**
     * The differ options. Overridden by stored compare settings when present.
     *
     * @var array<string, mixed>
     */
    protected array $differOptions = [
        'context' => 3,
        'ignoreCase' => false,
        'ignoreLineEnding' => false,
        'ignoreWhitespace' => false,
        'lengthLimit' => 20000,
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $rendererOptions = [
        'detailLevel' => 'line',
        'language' => 'eng',
        'lineNumbers' => true,
        'separateBlock' => true,
        'showHeader' => true,
        'spacesToNbsp' => false,
        'tabSize' => 4,
        'mergeThreshold' => 0.8,
        'cliColorization' => RendererConstant::CLI_COLOR_AUTO,
        'outputTagAsString' => false,
        'jsonEncodeFlags' => \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
        'wordGlues' => [' ', '-'],
        'resultForIdenticals' => null,
        'wrapperClasses' => ['diff-wrapper'],
    ];

    protected string $file1;
    protected string $file2;

    public function __construct(string $file1, string $file2)
    {
        $this->file1 = $file1;
        $this->file2 = $file2;

        $storedOptions = Setting::find(1)?->config_compare_settings;
        if (! empty($storedOptions)) {
            $this->differOptions = $storedOptions;
        }
    }

    /**
     * @return bool|string|array<string, mixed>
     */
    public function compare_configs(): bool|string|array
    {
        if (! $this->check_hash_match()) {
            return $this->file_content_compare();
        }

        return true;
    }

    public function check_hash_match(): bool
    {
        return sha1_file($this->file1) === sha1_file($this->file2);
    }

    /**
     * @return array<string, string>
     */
    public function file_content_compare(): array
    {
        if (! file_exists($this->file1) || ! file_exists($this->file2)) {
            return ['error' => 'Files do not exist for config compare'];
        }

        $htmlDiff = DiffHelper::calculateFiles(
            $this->file1,
            $this->file2,
            $this->rendererName,
            $this->differOptions,
            $this->rendererOptions
        );

        return [
            'diff' => $htmlDiff,
            'diff_type' => $this->calculateDiffTypeFromHtml($htmlDiff),
        ];
    }

    private function calculateDiffTypeFromHtml(string $htmlDiff): string
    {
        $hasAdd = str_contains($htmlDiff, '<ins>') || str_contains($htmlDiff, 'change-ins');
        $hasDel = str_contains($htmlDiff, '<del>') || str_contains($htmlDiff, 'change-del');

        return match (true) {
            $hasAdd && ! $hasDel => 'added',
            ! $hasAdd && $hasDel => 'deleted',
            $hasAdd && $hasDel => 'changed',
            default => 'none',
        };
    }
}
