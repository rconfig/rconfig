<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDeviceModelsRequest;
use App\Models\Device;
use App\Models\DeviceModels;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DeviceModelsController extends ApiBaseController
{
    public function __construct(DeviceModels $model, $modelname = 'device_models')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $this->updateDeviceModelsFromDevices();

        $response = QueryBuilder::for(DeviceModels::class)
            ->allowedFilters(['name'])
            ->defaultSort('name')
            ->allowedSorts(['id', 'name'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreDeviceModelsRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreDeviceModelsRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }

    public function deleteMany(Request $request)
    {
        $ids = $request->input('ids');
        $this->model::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Device Models deleted successfully'], 200);
    }

    protected function updateDeviceModelsFromDevices()
    {
        $distinctDeviceModels = Device::distinct()->pluck('device_model');

        foreach ($distinctDeviceModels as $deviceModel) {
            if (!DeviceModels::where('name', $deviceModel)->exists()) {
                DeviceModels::create(['name' => $deviceModel]);
            }
        }
    }
}
