<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Controllers\QueryFilters\QueryFilterMultipleFields;
use App\Jobs\CheckDeviceReachabilityJob;
use App\Jobs\DownloadConfigNowJob;
use App\Traits\MaskableCredentials;
use App\Models\Device;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DeviceController extends ApiBaseController
{
    use MaskableCredentials, RespondsWithHttpStatus;

    public function __construct(Device $model, $modelname = 'device')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null, $return = 0)
    {
        $allowedFields = ['*'];
        if (config('rConfig.mask_device_credentials')) {
            // select only columns to prevent leakage of device creds
            $excludedFields = ['device_username', 'device_password', 'device_enable_password'];
            // Get all the fields from the table and remove excluded fields
            $allFields = Schema::getColumnListing('devices');
            $allowedFields = array_diff($allFields, $excludedFields);
            // append $allowedFields to request
            // this is requirement for spatie query builder as the fields should be specified in the request
            $request->merge(['fields' => implode(',', $allowedFields)]);
        }

        $relationships = [
            'vendor',
            'category',
            'category.command',
            'tag',
            'template',
            'configSummary',
            'lastConfig',
        ];

        // Handle load more mode - when perPage is very large, use chunked loading
        $isLoadMoreMode = $request->has('loadMore') && $request->boolean('loadMore');
        $perPage = (int) $request->perPage;

        // If load more mode is detected but perPage is still very large, use a reasonable chunk size
        if ($isLoadMoreMode && $perPage >= 10000000) {
            $perPage = 50; // Use a reasonable chunk size for load more
        }

        $query = QueryBuilder::for(Device::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new QueryFilterMultipleFields, 'id, device_name, device_ip, device_model'),
                AllowedFilter::exact('category', 'category.id'),
                AllowedFilter::exact('vendor', 'vendor.id'),
                AllowedFilter::exact('tag', 'tag.id'),
                AllowedFilter::exact('device_model'),
                AllowedFilter::exact('status'),
            ])
            ->allowedFields($allowedFields)
            ->with($relationships)
            ->allowedSorts([
                'id',
                'created_at',
                'device_name',
                'device_ip',
                'device_model',
                'status',
                'last_config_at',
                'device_added_by',
            ]);

        // For load more mode, we might want to optimize the query further
        if ($isLoadMoreMode) {
            // You could add specific optimizations for load more mode here
            // For example, simpler relationships or lighter data loading
            $query->select($allowedFields);
        }

        $results = $query->paginate($perPage);

        // Add load more specific metadata to the response
        if ($isLoadMoreMode) {
            $results->appends(['loadMore' => true]);
        }

        return response()->json($results);
    }

    public function multiSelectIndex(Request $request)
    {
        // this function is specifically used in resources/js/pages/Shared/FormFields/DeviceMultiSelect.vue for super fast device search and load times
        // device_name is indexed
        $devices = QueryBuilder::for(Device::class)
            ->select(['id', 'device_name']) // Only fetch needed fields
            ->allowedSorts('device_name')
            ->allowedFilters([
                AllowedFilter::partial('device_name'),
                AllowedFilter::exact('id'),
            ])
            ->defaultSort('device_name') // Default alphabetical sort
            ->paginate($request->get('per_page', 50)); // Smaller page size for infinite scroll

        return response()->json($devices);
    }

    public function show($id, $relationship = null, $withCount = null, $return = 0)
    {
        $relationships = [
            'vendor',
            'category',
            'category.command',
            'tag',
            'template',
            'configSummary',
            'deviceCredName',
        ];

        $result = parent::show($id, $relationships);

        if ($result == null) {
            return $this->respondNotFound(' Device not found, or your role does not have access to this device.', 404);
        }

        $result = $this->maskCredsIfEnabled($result);

        return $result;
    }

    public function store(StoreDeviceRequest $request)
    {
        
        $updateArray = $request->toDTO()->toArray();

        unset(
            $updateArray['device_tags'],
            $updateArray['device_vendor'],
            $updateArray['selectedCategoryObj'],
            $updateArray['selectedModelObj'],
            $updateArray['selectedTemplateObj'],
            $updateArray['selectedVendorObj'],
            $updateArray['selectedTagObj'],
        );

        $model = parent::storeResource($updateArray, 1);

        $model->save();

        dispatch(new CheckDeviceReachabilityJob($model))->onQueue('rConfigDefault');

        $model->Tag()->attach($request->device_tags);
        $model->Vendor()->attach($request->device_vendor);
        $model->Category()->attach($request->device_category_id);
        $model->Template()->attach($request->device_template);

        dispatch(new DownloadConfigNowJob($model->id))->onQueue('ManualDownloadQueue');

        return $this->successResponse(
            Str::ucfirst($this->modelname) . ' created successfully!',
            ['id' => $model->id]
        );
    }

    public function update(StoreDeviceRequest $request, $id)
    {

        // Build update array from DTO
        $updateArray = $request->toDTO()->toArray();

        // Remove fields handled separately (not part of direct update)
        unset(
            $updateArray['device_tags'],
            $updateArray['device_vendor'],
            $updateArray['selectedCategoryObj'],
            $updateArray['selectedModelObj'],
            $updateArray['selectedTemplateObj'],
            $updateArray['selectedVendorObj'],
            $updateArray['selectedTagObj'],
        );

        // Avoid overwriting values with nulls in PostgreSQL
        foreach (['status', 'last_seen'] as $nullableField) {
            if (is_null($updateArray[$nullableField] ?? null)) {
                unset($updateArray[$nullableField]);
            }
        }

        $model = parent::updateResource($id, $updateArray, 1);

        $model->Tag()->sync($request->device_tags);
        $model->Vendor()->sync($request->device_vendor);
        $model->Category()->sync($request->device_category_id);
        $model->Template()->sync($request->device_template);

        return $this->successResponse(
            Str::ucfirst($this->modelname) . ' edited successfully!',
            ['id' => $model->id]
        );
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->delete();
        $model->Tag()->detach();
        $model->Vendor()->detach();
        $model->Category()->detach();
        $model->Template()->detach();

        // configs are deleted in the Device Observer

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    public function allDeviceNames()
    {

        $collection = Device::select('id', 'device_name')->where('status', '!=', '100')->get();

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
        dispatch(new CheckDeviceReachabilityJob($model))->onQueue('rConfigDefault');

        $model->save();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' enabled successfully!', ['id' => $model->id]);
    }
}
