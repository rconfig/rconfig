<?php

namespace App\Services\Config\Search\SearchStrategies;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LatestSearchStrategyNew
{

    public function searchConfigurations(array $searchParams): array
    {
        $configs = $this->getFilteredConfigs($searchParams);

        if (empty($searchParams['search_string'])) {
            return [];
        }

        return $this->searchInConfigs($configs, $searchParams);
    }

    /**
     * Get filtered configurations based on search parameters
     */
    private function getFilteredConfigs(array $params): \Illuminate\Support\Collection
    {
        $query = DB::table('configs')
            ->orderBy('created_at', 'desc');

        $this->applyDeviceFilters($query, $params);
        $this->applyVersionFilter($query, $params);
        $this->applyDateFilters($query, $params);
        $this->applyCommandFilter($query, $params);

        return $query->get();
    }

    /**
     * Apply device name and category filters
     */
    private function applyDeviceFilters($query, array $params): void
    {
        if (! empty($params['device_name'])) {
            $query->where('device_name', 'like', "%{$params['device_name']}%");
        }

        if (! empty($params['device_category'])) {
            $query->where('device_category', 'like', "%{$params['device_category']}%");
        }
    }

    /**
     * Apply latest version filter
     */
    private function applyVersionFilter($query, array $params): void
    {
        if (isTrue($params['latest_version_only'] ?? false)) {
            $query->where('latest_version', 1);
        }
    }

    /**
     * Apply date range filters
     */
    private function applyDateFilters($query, array $params): void
    {
        if (! empty($params['start_date'])) {
            $query->where('start_time', '>=', Carbon::parse($params['start_date']));
        }

        if (! empty($params['end_date'])) {
            $query->where('start_time', '<=', Carbon::parse($params['end_date']));
        }
    }

    /**
     * Apply command filter
     */
    private function applyCommandFilter($query, array $params): void
    {
        if (! empty($params['command'])) {
            $query->where('command', $params['command']);
        }
    }

    /**
     * Search for the specified string within configuration files
     */
    private function searchInConfigs(\Illuminate\Support\Collection $configs, array $params): array
    {
        $searchOptions = $this->extractSearchOptions($params);
        $matches = [];

        foreach ($configs as $config) {
            $fileMatches = $this->searchInConfigFile($config, $searchOptions);

            if (! empty($fileMatches)) {
                $matches[] = $this->buildMatchResult($config, $fileMatches);
            }
        }

        return $matches;
    }

    /**
     * Extract search options from parameters
     */
    private function extractSearchOptions(array $params): array
    {
        return [
            'search_string' => $params['search_string'],
            'lines_before' => (int) ($params['lines_before'] ?? 0),
            'lines_after' => (int) ($params['lines_after'] ?? 0),
            'ignore_case' => isTrue($params['ignore_case'] ?? false),
        ];
    }

    /**
     * Search for matches within a single configuration file
     */
    private function searchInConfigFile($config, array $searchOptions): array
    {
        $filePath = $config->config_location;

        if (! file_exists($filePath)) {
            return [];
        }

        $fileContents = $this->getFileContents($filePath);

        return $this->findMatches($fileContents, $searchOptions);
    }

    /**
     * Get file contents as array of lines
     */
    private function getFileContents(string $filePath): array
    {
        return explode("\n", File::get($filePath));
    }

    /**
     * Find all matches in file contents
     */
    private function findMatches(array $fileContents, array $searchOptions): array
    {
        $matches = [];
        $totalLines = count($fileContents);

        foreach ($fileContents as $lineNumber => $line) {
            if ($this->lineContainsSearchString($line, $searchOptions)) {
                $matches[] = [
                    'line_number' => $lineNumber + 1, // Human-readable line numbers start from 1
                    'context' => $this->extractContext(
                        $fileContents,
                        $lineNumber,
                        $totalLines,
                        $searchOptions
                    ),
                ];
            }
        }

        return $matches;
    }

    /**
     * Check if a line contains the search string
     */
    private function lineContainsSearchString(string $line, array $searchOptions): bool
    {
        $searchString = $searchOptions['search_string'];

        return $searchOptions['ignore_case']
            ? stripos($line, $searchString) !== false
            : strpos($line, $searchString) !== false;
    }

    /**
     * Extract context lines around a match
     */
    private function extractContext(array $fileContents, int $matchLineNumber, int $totalLines, array $searchOptions): array
    {
        $start = max(0, $matchLineNumber - $searchOptions['lines_before']);
        $end = min($totalLines - 1, $matchLineNumber + $searchOptions['lines_after']);

        $contextLines = array_slice($fileContents, $start, $end - $start + 1);

        // Escape HTML and add ellipsis
        $contextLines = array_map('htmlspecialchars', $contextLines);
        $contextLines[] = '...';

        return $contextLines;
    }

    /**
     * Build the final match result structure
     */
    private function buildMatchResult($config, array $fileMatches): array
    {
        // Get the last match for backward compatibility
        $lastMatch = end($fileMatches);

        return [
            'id' => $config->id,
            'device_name' => $config->device_name,
            'device_category' => $config->device_category,
            'command' => $config->command,
            'file' => $config->config_location,
            'config_filesize' => $config->config_filesize,
            'latest_version' => $config->latest_version ?? null,
            'line_number' => $lastMatch['line_number'],
            'context' => implode("\n", $lastMatch['context']),
            'created_at' => $config->created_at,
            'matches' => $fileMatches, // All matches grouped by file
        ];
    }
}
