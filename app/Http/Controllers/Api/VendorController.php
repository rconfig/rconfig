<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class VendorController extends ApiBaseController
{
    public function __construct(Vendor $model, $modelname = 'vendor')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null, $return = 0)
    {
        $response = QueryBuilder::for(Vendor::class)
            ->with(['device'])
            ->allowedFilters(['vendorName'])
            ->defaultSort('-id')
            ->allowedSorts('id', 'vendorName')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreVendorRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreVendorRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        try {
            $category = Vendor::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Vendor deleted successfully.'], 200);
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
            $vendor = Vendor::whereIn('id', $ids)->get();

            // need to run each vendor through the boot method
            foreach ($vendor as $vendor) {
                $vendor->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => 'Vendors deleted successfully'], 200);
    }
}
