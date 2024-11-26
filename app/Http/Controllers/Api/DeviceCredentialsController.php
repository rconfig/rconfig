<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreDeviceCredentialsRequest;
use App\Models\DeviceCredentials;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class DeviceCredentialsController extends ApiBaseController
{
    public function __construct(DeviceCredentials $model, $modelname = 'device_credentials')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $response = QueryBuilder::for(DeviceCredentials::class)
            ->with('device')
            ->allowedFilters(['cred_name'])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'cred_name'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreDeviceCredentialsRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['device']);
    }

    public function update($id, StoreDeviceCredentialsRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        try {
            $deviceCredentials = DeviceCredentials::findOrFail($id);
            $deviceCredentials->delete();

            return response()->json(['message' => 'Device credentials deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred.',
            ], 500); // For other exceptions, use 500
        }
    }

    public function deleteMany(Request $request)
    {
        try {
            $ids = $request->ids;
            \DB::beginTransaction();
            $deviceCredentials = DeviceCredentials::whereIn('id', $ids)->get();

            // need to run each category through the boot method
            foreach ($deviceCredentials as $deviceCredential) {
                $deviceCredential->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => 'Device credentials deleted successfully'], 200);
    }
}
