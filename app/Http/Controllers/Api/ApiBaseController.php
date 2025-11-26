<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as Controller;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiBaseController extends Controller
{
    use RespondsWithHttpStatus;

    protected $query;

    protected $model;

    protected $modelname;

    protected $matchThese;

    public function __construct(Model $model, $modelname)
    {
        $this->query;
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $searchCols, $relationship = null, $withCount = null)
    {
        $sortCol = $request->sortCol != '' ? $request->sortCol : 'created_at';
        $sortOrd = $request->sortOrd != '' ? $request->sortOrd : 'desc';

        $this->query = $this->model::query();
        $this->query->orderBy($sortCol, $sortOrd);

        if ($relationship != null) {
            foreach ($relationship as $rel) {
                $this->query->with($rel);
            }
        }

        if ($withCount != null) {
            foreach ($withCount as $rel) {
                $this->query->withCount($rel);
            }
        }

        if ($request->filter != null) {
            $this->return_filtered_data($request->filter, $searchCols);
        }

        $data = $this->query->paginate($request->perPage);

        return $data;
    }

    protected function return_filtered_data($filter, $searchCols)
    {
        // for filtering objects contained within the filter parameter:: filter={%22device_id%22:%221001%22}
        if ($this->is_valid_json($filter)) {
            $filter = json_decode($filter);
            $key = key((array) ($filter));

            if ($key === 'category' || $key === 'vendor' || $key === 'tag') {
                $this->query->whereRelation($key, 'id', $filter->$key)->get();
            } else {
                $this->query->where($key, $filter->$key);
            }
        } else {
            foreach ($searchCols as $col) {
                $this->matchThese[] = [$col, 'like', '%' . $filter . '%'];
            }
            //Add Conditions
            foreach ($this->matchThese as $key => $matchThis) {
                if ($key === array_key_first($this->matchThese)) {
                    $this->query->where($matchThis[0], $matchThis[1], $matchThis[2]);
                } else {
                    $this->query->orWhere($matchThis[0], $matchThis[1], $matchThis[2]);
                }
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     * In order to avoid an inherant bug in laravel, we need to pass the request array directly to a newly name method.
     * Thats why this is called StoreResource instead of Store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeResource($request, $return = 0)
    {
        $model = $this->model::create($request);
        if ($return === 1) {
            return $model;
        }

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $this->query = $this->model::query();
        $this->query->findOrFail($id);

        if ($relationship != null) {
            foreach ($relationship as $rel) {
                $this->query->with($rel);
            }
        }

        if ($withCount != null) {
            foreach ($withCount as $rel) {
                $this->query->withCount($rel);
            }
        }

        return $this->query->first();
    }

    /**
     * Update the specified resource in storage.
     * In order to avoid an inherant bug in laravel, we need to pass the request array directly to a newly name method.
     * Thats why this is called UpdateResource instead of Update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function updateResource($id, $request, $return = 0)
    {
        $model = $this->model::find($id);
        $model = tap($model)->update($request); // tap returns model instead of boolean

        if ($return === 1) {
            return $model;
        }

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        $model = $this->model::find($id);
        $model = tap($model)->delete(); // tap returns model instead of boolean

        if ($return === 1) {
            return $model;
        }

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    public function deleteMany(Request $request)
    {
        try {
            $ids = $request->ids;
            \DB::beginTransaction();
            $items = $this->model::whereIn('id', $ids)->get();

            // need to run each vendor through the boot method
            foreach ($items as $item) {
                $item->delete();
            }
            \DB::commit();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['message' => $this->modelname . 'deleted successfully'], 200);
    }

    private function is_valid_json($string)
    {
        return is_object(json_decode($string));
    }
}
