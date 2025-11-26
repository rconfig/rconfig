<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\QueryFilters\WithDevicesFilter;
use App\Http\Requests\StoreDeviceModelRequest;
use App\Models\Device;
use App\Models\DeviceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DeviceModelController extends ApiBaseController
{
    public function __construct(DeviceModel $model, $modelname = 'device model')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $perPage = (int) $request->perPage ?: 15;

        $query = QueryBuilder::for(DeviceModel::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'name'),
                AllowedFilter::custom('with_devices', new WithDevicesFilter),
            ])
            ->allowedSorts(['id', 'name', 'created_at', 'devices_count'])
            ->withCount('devices');

        // Filter by devices if requested
        if ($request->filled('with_devices') && $request->with_devices) {
            $query->has('devices');
        }

        $result = $query->paginate($perPage);

        return response()->json($result);
    }

    public function store(StoreDeviceModelRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreDeviceModelRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }

    protected function updateDeviceModelsFromDevices()
    {
        $distinctDeviceModels = Device::distinct()->pluck('device_model');

        foreach ($distinctDeviceModels as $deviceModel) {
            if (!DeviceModel::where('name', $deviceModel)->exists()) {
                DeviceModel::create(['name' => $deviceModel]);
            }
        }
    }
}
