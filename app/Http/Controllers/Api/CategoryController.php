<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends ApiBaseController
{
    public function __construct(Category $model, $modelname = 'category')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $response = QueryBuilder::for(Category::class)
            ->with('command', 'device')
            ->allowedFilters(['categoryName'])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'categoryName'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreCategoryRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['command'], ['device']);
    }

    public function update($id, StoreCategoryRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully.'], 200);
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
            $categories = Category::whereIn('id', $ids)->get();

            // need to run each category through the boot method
            foreach ($categories as $category) {
                $category->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => 'Command groups deleted successfully'], 200);
    }
}
