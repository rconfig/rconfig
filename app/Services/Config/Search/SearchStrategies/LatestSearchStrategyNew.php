<?php

namespace App\Services\Config\Search\SearchStrategies;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LatestSearchStrategyNew
{

    public function __construct() {}


    function searchConfigurations($deviceName, $deviceCategory, $command, $searchString, $linesBefore = 2, $linesAfter = 2, $latestVersionOnly = true, $startDate = null, $endDate = null, $ignoreCase = true)
    {
        // Query builder for configs
        $query = DB::table('configs')
            ->where('device_name', 'like', "%$deviceName%")
            ->where('device_category', 'like', "%$deviceCategory%");

        // Filter for latest version
        // if ($latestVersionOnly) {
        //     // only take records with the latest created_at timestamp
        //     $query->select('device_id', DB::raw('max(created_at) as created_at'))
        //         ->groupBy('device_id');
        //     $query = DB::table('configs')
        //         ->joinSub($query, 'latest_configs', function ($join) {
        //             $join->on('configs.device_id', '=', 'latest_configs.device_id')
        //                 ->on('configs.created_at', '=', 'latest_configs.created_at');
        //         });
        // }

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

        // Get filtered configs
        $configs = $query->get();

        $matches = [];

        // Search within each configuration file for the specific string
        foreach ($configs as $config) {
            $filePath = $config->config_location;

            // Check if file exists
            if (file_exists($filePath)) {
                // Read file line by line
                // $fileContents = file($filePath, FILE_IGNORE_NEW_LINES);
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

        return $matches;
    }
}
