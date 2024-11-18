<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDeviceRequest;
use App\Jobs\DownloadConfigNow;
use App\Models\Device;
use App\Models\DeviceComment;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DeviceController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(Device $model, $modelname = 'device')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['id', 'device_name', 'device_ip', 'device_model'];

        $result = QueryBuilder::for(Device::class)
            ->with(['vendor', 'category', 'category.command', 'tag', 'template', 'lastConfig', 'comments'])
            ->withCount(['config_good', 'config_bad', 'config_unknown'])
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'id, device_name, device_ip'),
                AllowedFilter::exact('category', 'category.id'),
                AllowedFilter::exact('vendor', 'vendor.id'),
                AllowedFilter::exact('tag', 'tag.id'),
                AllowedFilter::exact('status'),
            ])
            ->defaultSort('-id')
            ->allowedSorts('id', 'device_name', 'device_ip', 'status')
            ->paginate((int) $request->perPage);

        // return $item;
        // $result = parent::index($request, $searchCols, ['vendor', 'category',  'category.command', 'tag', 'template', 'lastConfig'], ['config_good', 'config_bad', 'config_unknown']);

        return response()->json($result);
    }

    public function store(StoreDeviceRequest $request)
    {

        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->Tag()->attach($request->device_tags);
        $model->Vendor()->attach($request->device_vendor);
        $model->Category()->attach($request->device_category_id);
        $model->Template()->attach($request->device_template);

        $this->dispatch(new DownloadConfigNow($model->id))->onQueue('ManualDownloadQueue');

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        $results = parent::show($id, ['vendor', 'category', 'deviceCred', 'category.command', 'tag', 'template', 'lastConfig'], ['config_good', 'config_bad', 'config_unknown']);

        return $results;
    }

    public function commentsByDeviceId($deviceid)
    {
        $results = DeviceComment::where('device_id', $deviceid)->with('user')->get();

        return response()->json($results);
    }

    public function update($id, StoreDeviceRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);

        $model->Tag()->sync($request->device_tags);
        $model->Vendor()->sync($request->device_vendor);
        $model->Category()->sync($request->device_category_id);
        $model->Template()->sync($request->device_template);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!', ['id' => $model->id]);
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->delete();
        $model->Tag()->detach();
        $model->Vendor()->detach();
        $model->Category()->detach();
        $model->Template()->detach();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    public function deleteMany(Request $request)
    {
        $ids = $request->input('ids');
        $this->model::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Devices deleted successfully'], 200);
    }

    public function getDeviceModels()
    {
        $collection = Device::select('device_model')->whereNotNull('device_model')->groupBy('device_model')->get();
        $plucked = $collection->pluck('device_model');

        return $this->successResponse('All Device Models!', $plucked);
    }

    public function allDeviceNames()
    {
        $collection = Device::select('id', 'device_name')->get();

        return $this->successResponse('All Device Names!', $collection);
    }


    public function disable($id)
    {

        $model = Device::findOrFail($id);
        $model->status = 100;
        $model->save();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' disabled successfully!', ['id' => $model->id]);
    }

    public function enable($id)
    {

        $model = Device::findOrFail($id);

        // ping device
        $ping = ping($model->device_ip);
        if (! $ping) {
            $model->status = 0;
        } else {
            $model->status = 1;
        }

        $model->save();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' enabled successfully!', ['id' => $model->id]);
    }
}
