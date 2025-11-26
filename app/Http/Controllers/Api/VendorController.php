<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
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
        $searchCols = ['vendorName'];

        $query = QueryBuilder::for($this->model::class)
            ->with(['device:id,device_name'])
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'vendorName'),
            ])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'vendorName', 'created_at', 'updated_at'])
            ->paginate($request->perPage ?? 10);

        return response()->json($query);
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
        return parent::destroy($id);
    }
}
