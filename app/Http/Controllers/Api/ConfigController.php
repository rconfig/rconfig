<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\FilterMultipleFields;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ConfigController extends ApiBaseController
{
    public function __construct(Config $model, $modelname = 'config')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $response = QueryBuilder::for(Config::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'id, device_name, command'),
                AllowedFilter::exact('download_status'),
            ])
            ->defaultSort('-id')
            ->allowedSorts('id', 'device_name', 'command', 'download_status')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        if (File::exists($model->config_location)) {
            File::delete($model->config_location);
            $logmsg = 'Config File : ' . $model->config_location . ' was deleted';
        } else {
            $logmsg = 'Unable to find file from path: ' . $model->config_location;
            $this->failureResponse($logmsg);
        }

        activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'config', '', $model->device_id, 'device');

        return $this->successResponse($logmsg);
    }

    public function getAllById($id, Request $request)
    {

        $response = QueryBuilder::for(Config::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'id, device_name, command', 'download_status'),
                AllowedFilter::exact('device_id'),
                AllowedFilter::exact('download_status'),
            ])
            ->where('device_id', $id)
            ->defaultSort('-id')
            ->allowedSorts('id', 'device_name', 'command', 'download_status')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function getLatestById($id)
    {
        $results = DB::select('select * from configs WHERE id IN (SELECT MAX(id) FROM configs WHERE device_id = ? GROUP BY command)', [$id]);

        return $this->successResponse('Success', $results);
    }

    public function getSingleConfigFile($id)
    {
        if (! $id) {
            return $this->failureResponse('No ID provided');
        }

        $config_location = Config::find($id)->config_location;

        if ($config_location) {
            $content = File::get($config_location);
            $utf8_string = mb_convert_encoding($content, 'UTF-8');

            $result = [
                'content' => $utf8_string,
                'config_location' => $config_location,
            ];

            return $this->successResponse('Success', $result);
        } else {
            return $this->failureResponse('No config file found');
        }
    }

    public function getDistinctCommands($id)
    {
        if ($id == 0) {
            $result = Config::distinct()->get(['command']);
        } else {
            $result = Config::where('device_id', $id)->distinct()->get(['command']);
        }

        return $this->successResponse('Success', $result);
    }
}
