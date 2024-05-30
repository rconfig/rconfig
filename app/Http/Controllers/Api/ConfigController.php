<?php

namespace App\Http\Controllers\Api;

use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Response;

class ConfigController extends ApiBaseController
{
    public function __construct(Config $model, $modelname = 'config')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = [''];

        return response()->json(parent::index($request, $searchCols));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Command  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        if (File::exists($model->config_location)) {
            File::delete($model->config_location);
            $logmsg = 'Config File : '.$model->config_location.' was deleted';
        } else {
            $logmsg = 'Unable to find file from path: '.$model->config_location;
            $this->failureResponse($logmsg);
        }

        activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'config', '', $model->device_id, 'device');

        return $this->successResponse($logmsg);
    }

    /**
     * Display a listing of the resource for a given device ID.
     *
     * @param  \App\Models\Request  $tag
     * @return \Illuminate\Http\Response
     */
    public function getAllById($id, $status, Request $request)
    {
        $sortCol = $request->sortCol != '' ? $request->sortCol : 'created_at';
        $sortOrd = $request->sortOrd != '' ? $request->sortOrd : 'desc';

        $searchCols = ['command'];

        $perPage = (int) $request->perPage;
        // dd($perPage);

        $this->query = $this->model::query();
        $this->query->orderBy($sortCol, $sortOrd);

        if ($id != 0) {
            $this->query->where('device_id', $id);
        }

        if (! $status || $status != 'all') {
            $this->query->where('download_status', $status);
        }
        if ($request->filter != null) {
            $this->return_filtered_data($request->filter, $searchCols);
        }

        return $this->query->orderBy('created_at', 'desc')->paginate($perPage);
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
            
            return $this->successResponse('Success', $content);
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

    // public function getOneById(Request $request)
    // {
    //     $model = Config::find($request->id);
    //     return $model;
    // }

    // public function getAllInvalid(Request $request)
    // {
    //     $perPage = (int) $request->items;
    //     $data = Config::orderBy('created_at', 'desc')->where('download_status', 0)->orderBy('created_at', 'desc')->paginate($perPage);
    //     return response()->json($data);
    // }

    // public function getAllInvalidById(Request $request)
    // {
    //     $perPage = (int) $request->items;
    //     $data = Config::where('device_id', $request->device_id)->where('download_status', null)->orWhere('download_status', '=', 0)->orderBy('created_at', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);
    //     return response()->json($data);
    // }

    // public function getAllByCommandString($command, Request $request)
    // {
    //     $perPage = (int) $request->items;
    //     if ($request->filter != 'null') {
    //         $data = $this->return_filtered_data_by_id($request->filter, $perPage, $request->device_id);
    //     } else {
    //         $data = Config::where('device_id', $request->device_id)->where('command', $command)->orderBy('created_at', 'desc')->paginate($perPage);
    //     }
    //     return response()->json($data);
    // }

    // public function getOneById(Request $request)
    // {
    //     $model = Config::find($request->id);
    //     return $model;
    // }

    // public function getLatestConfigDate()
    // {
    //     return Config::select('created_at')->latest()->first();
    // }

    // public function getConfigsStatsByDeviceId(Request $request)
    // {
    //     $resultArr['count_valid'] = Config::where('device_id', $request->id)->where('download_status', 1)->count();
    //     $invalid_collection = Config::where('device_id', $request->id)->get();
    //     $resultArr['count_invalid'] = $invalid_collection->where('download_status', null)->where('download_status', 0)->count();
    //     $resultArr['latest_download'] = Config::select('start_time')->where('device_id', $request->id)->orderBy('created_at', 'desc')->first();
    //     $resultArr['latest_download'] = $resultArr['count_valid'] != null ? $resultArr['count_valid'] : 'NULL';
    //     return $resultArr;
    // }

    // public function getConfigsInfoByDeviceId(Request $request)
    // {
    //     $resultArr = array();
    //     $uniqueCommands = Config::select('command')->where('device_id', $request->id)->distinct('command')->get();
    //     $timezone = \Config::get('app.timezone');
    //     $now = Carbon::now($timezone);

    //     $i = 0;
    //     foreach ($uniqueCommands as $uniqueCommand) {
    //         $latestCommand = Config::select('id', 'download_status', 'start_time')
    //             ->where('command', $uniqueCommand->command)
    //             ->where('device_id', $request->id)

    //             ->orderBy('created_at', 'desc')
    //             ->first();
    //         // $initialCommand = Config::select('id', 'download_status', 'start_time')->where('command', $uniqueCommand->command)->where('device_id', $request->id)->orderBy('created_at', 'asc')->first();
    //         $count = Config::where('command', $uniqueCommand->command)->where('device_id', $request->id)->where('download_status', 1)->count();
    //         $resultArr['commands'][$i]['id'] = $latestCommand->id;
    //         $resultArr['commands'][$i]['command'] = $uniqueCommand->command;
    //         $resultArr['commands'][$i]['count'] = $count;
    //         $resultArr['commands'][$i]['latest_config_time'] = Carbon::createFromTimestamp(strtotime($latestCommand->start_time))
    //             ->addHours($timezone)
    //             ->format('M d, Y G:iA');
    //         // $resultArr['commands'][$i]['initial_config_time'] = Carbon::createFromTimestamp(strtotime($initialCommand->start_time))
    //         //     ->addHours($timezone)
    //         //     ->format('M d, Y G:iA');
    //         $resultArr['commands'][$i]['download_status'] = $latestCommand->download_status;
    //         $i++;
    //     }
    //     return $resultArr;
    // }

    // private function return_filtered_data($filter, $perPage)
    // {
    //     return Config::orderBy('created_at', 'desc')
    //         ->where('command', 'like', '%' . $filter . '%')
    //         ->paginate($perPage);
    // }

    // private function return_filtered_data_by_id($filter, $perPage, $id)
    // {
    //     return Config::orderBy('created_at', 'desc')
    //         ->where('device_id', $id)
    //         ->where('command', 'like', '%' . $filter . '%')
    //         ->paginate($perPage);
    // }
}
