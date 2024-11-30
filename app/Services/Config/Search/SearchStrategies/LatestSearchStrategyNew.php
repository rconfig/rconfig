<?php

namespace App\Services\Config\Search\SearchStrategies;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LatestSearchStrategyNew
{

    public function __construct() {}


    public function searchConfigurations($deviceName, $deviceCategory, $command, $searchString, $linesBefore = 2, $linesAfter = 2, $latestVersionOnly = true, $startDate = null, $endDate = null, $ignoreCase = true, $page = 1, $perPage = 10)
    {
        // Query builder for configs
        $query = DB::table('configs')
            ->where('device_name', 'like', "%$deviceName%")
            ->where('device_category', 'like', "%$deviceCategory%");

        // Filter for latest version
        if ($latestVersionOnly) {
            $query->where('latest_version', 1);
        }

        // Filter by date range
        if ($startDate) {
            $query->where('start_time', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('start_time', '<=', Carbon::parse($endDate));
        }

        if ($command) {
            $query->where('command', $command);
        }

        // Paginate the results

        $configs = $query->paginate($perPage, ['*'], 'page', $page);

        $matches = [];

        // Search within each configuration file for the specific string
        foreach ($configs as $config) {
            $filePath = $config->config_location;

            // Check if file exists
            if (file_exists($filePath)) {
                // Read file line by line
                $fileContents = explode("\n", File::get($filePath));
                $fileLinesCount = count($fileContents);

                $fileMatches = []; // Store all matches for this file

                // Loop through file lines and find the match
                foreach ($fileContents as $lineNumber => $line) {
                    $found = $ignoreCase ? stripos($line, $searchString) !== false : strpos($line, $searchString) !== false;

                    if ($found) {
                        // Capture lines before and after
                        $start = max(0, $lineNumber - $linesBefore);
                        $end = min($fileLinesCount - 1, $lineNumber + $linesAfter);

                        // Extract matching block
                        $matchBlock = array_slice($fileContents, $start, $end - $start + 1);

                        // Append this match block to file matches
                        $fileMatches[] = [
                            'line_number' => $lineNumber + 1, // For human readability, line numbers start from 1
                            'context' => implode("\n", $matchBlock),
                        ];
                    }
                }

                // If there were any matches in this file, store them as a single grouped result
                if (!empty($fileMatches)) {
                    $matches[] = [
                        'id' => $config->id,
                        'device_name' => $config->device_name,
                        'device_category' => $config->device_category,
                        'file' => $filePath,
                        'config_filesize' => $config->config_filesize,
                        'line_number' => $lineNumber + 1,
                        'context' => implode("\n", $matchBlock),
                        'config_date' => $config->created_at,
                        'matches' => $fileMatches,  // Grouped matches from this file
                    ];
                }
            }
        }

        return [
            'current_page' => $configs->currentPage(),
            'last_page' => $configs->lastPage(),
            'per_page' => $configs->perPage(),
            'total' => $configs->total(),
            'data' => $matches,
        ];
    }
}
