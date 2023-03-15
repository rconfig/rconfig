<?php

namespace App\CustomClasses;

use App\Models\Category;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ConfigSearch
{
    protected $categoryId;

    protected $categoryName;

    protected $string;

    protected $linecount;

    protected $latestOnly;

    public function __construct($categoryId, $string, $linecount, $latestOnly)
    {
        $this->categoryId = $categoryId;
        $this->string = $string;
        $this->linecount = $linecount;
        $this->latestOnly = isset($latestOnly) ? $latestOnly : false;
    }

    public function search()
    {
        $results = null;
        if ($this->latestOnly === false) {
            $results = $this->globalSearch();
        } elseif ($this->latestOnly === true) {
            $results = $this->latestSearch();
        }

        return $results;
    }

    private function latestSearch()
    {
        $this->categoryName = Category::where('id', $this->categoryId)->first()->categoryName;
        $deviceIdsForGivenCat = Config::where('device_category', $this->categoryName)->distinct()->pluck('device_id');

        $latestConfigsArray = [];
        foreach ($deviceIdsForGivenCat as $device) {
            $created_at = DB::table('configs')->where('device_id', $device)->max('created_at');
            $latestConfigsArray[] = Config::where('created_at', $created_at)->get();
        }
        $latestConfigsArray = Arr::flatten($latestConfigsArray);

        $allResultsArr = [];
        $matchCount = 0;
        foreach ($latestConfigsArray as $latestConfigItem) {
            $grepSearchCommand = 'grep '.escapeshellarg($this->string).' '.$latestConfigItem->config_location.' -C'.$this->linecount;
            exec($grepSearchCommand, $searchresults);
            //$searchresults0 & 1 are intended to mimic the output from the $searchresults arary in the globalSearch func
            if (! empty($searchresults)) {
                array_unshift($searchresults, '', '-::'.$latestConfigItem->config_location.'::-'); // move these to the top of the array
                $allResultsArr[] = $searchresults;
                $matchCount++;
            }
            unset($searchresults);
        }

        $allResultsArr = Arr::flatten($allResultsArr);

        $results = [];
        $start_time = Carbon::now();
        $results['lineCount'] = count($allResultsArr); // take away duration & linecount keys
        $results['matchCount'] = $matchCount;

        $results['fileCount'] = $matchCount;
        $end_time = Carbon::now();
        $results['duration'] = $start_time->diffInMilliseconds($end_time).'ms';
        $results['search_results'] = $allResultsArr;

        return $results;
    }

    public function globalSearch()
    {
        $awkSearchCommand = 'find '.config_data_path().$this->categoryName.' -type f -name "*.txt" | xargs grep -n -C'.$this->linecount.' '.escapeshellarg($this->string).' | awk -f '.rconfig_app_path().'search.awk';

        $totalMatchesCommand = 'grep -ro '.escapeshellarg($this->string).' '.config_data_path().'/ | wc -l ';
        $results = [];
        $start_time = Carbon::now();
        exec($awkSearchCommand, $searchresults);
        exec($totalMatchesCommand, $matchCount);
        $results['lineCount'] = count($searchresults); // take away duration & linecount keys
        $results['matchCount'] = (int) $matchCount[0]; // take away duration & linecount keys
        $results['fileCount'] = count(array_filter($searchresults, function ($v) {
            return substr($v, 0, 3) === '-::';
        }));
        $end_time = Carbon::now();
        $results['duration'] = $start_time->diffInMilliseconds($end_time).'ms';
        $results['search_results'] = $searchresults;

        return $results;
    }
}
