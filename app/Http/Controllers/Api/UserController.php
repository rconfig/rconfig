<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends ApiBaseController
{
    public function __construct(User $model, $modelname = 'user')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $response = QueryBuilder::for(User::class)
            ->allowedFilters(['name'])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'name', 'email', 'last_login'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreUserRequest $request)
    {
        $request->is_socialite_approved = 1;

        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreUserRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }

    public function setSocialiteApprovalStatus($userid, Request $request)
    {
        // $this->authorize('user.update');
        $status = $request->input('status');

        $user = User::find($userid);
        $user->is_socialite_approved = $status;
        $user->save();

        return response()->json(['status' => 'success']);
    }

    public function addExternalLink(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        $externalLinks = $user->external_links ?? [];
        $externalLinks[] = [
            'name' => $request->input('name'),
            'url' => $request->input('url'),
            'icon' => $request->input('icon'),
        ];

        $user->external_links = $externalLinks;
        $user->save();

        return response()->json($user->external_links);
    }

    public function removeExternalLink(Request $request)
    {
        $user = Auth::user();

        // Decode the JSON data
        $externalLinks = $user->external_links;
        $decodedName = urldecode($request->name);

        // Find the index of the link by name
        $linkIndex = array_search($decodedName, array_column($externalLinks, 'name'));

        // Check if the link exists
        if ($linkIndex === false) {
            return response()->json([
                'message' => 'Link not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Remove the link by the found index
        array_splice($externalLinks, $linkIndex, 1);

        // Encode the updated links back to JSON and save them
        $user->external_links = empty($externalLinks) ? null : $externalLinks;
        $user->save();

        return response()->json($user->external_links);
    }

    public function getExternalLinks($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->external_links);
    }
}
