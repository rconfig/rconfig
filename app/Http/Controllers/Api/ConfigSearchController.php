<?php

namespace App\Http\Controllers\Api;

use App\CustomClasses\ConfigSearch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConfigSearchController extends Controller
{
    public function search(Request $request)
    {
        Validator::make($request->all(), [
            'category' => 'required|exists:categories,id',
            'line_count' => 'required',
            'search_string' => 'required',
        ])->validate();

        $searchArr = (new ConfigSearch($request['category'], $request['search_string'], $request['line_count'], isset($request['latestOnly']) ? $request['latestOnly'] : false))->search();
        $searchArr['search_results'] = $this->recursive($searchArr['search_results']);

        return json_encode($searchArr);
    }

    private function recursive($in_array)
    {
        $array = [];
        $tmpArray = [];
        $k = 0;
        foreach ($in_array as $key => $value) {
            if (Str::startsWith($value, '-::')) {
                if (isset($tmpArray)) {
                    unset($tmpArray);
                }
                $k++;
                $value = Str::replaceFirst('-::', '', $value);
                $value = Str::replaceFirst('::-', '', $value);
                $value = array_reverse(explode('/', $value));
                $tmpArray[0] = $value;
            } else {
                if ($value === '') {
                    continue;
                }
                $tmpArray[] = $value;
            }
            $array[$k] = $tmpArray;
        }

        return $array;
    }
}
