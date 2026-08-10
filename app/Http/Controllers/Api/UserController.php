<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
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

        $query = QueryBuilder::for($this->model::class)
            ->allowedFilters(...[
                AllowedFilter::custom('q', new FilterMultipleFields, 'id, name, username, email'),
            ])
            ->defaultSort('-id')
            ->allowedSorts(...['id', 'email', 'name', 'last_login'])
            ->paginate($request->perPage ?? 10);

        return response()->json($query);
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

    /**
     * Return the authenticated user's own record.
     *
     * Self service counterpart to the Admin only show() above, so the profile
     * screen does not need access to the user administration endpoints.
     */
    public function profile()
    {
        return response()->json(Auth::user());
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->save();

        return response()->json(['status' => 'success']);
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }

    /**
     * Set the notification preference for the authenticated user.
     *
     * The {userid} route segment is ignored. It is retained only so existing
     * clients keep working, and carries no authority over which account changes.
     */
    public function setNotificationStatus($userid, Request $request)
    {
        $user = Auth::user();
        $user->get_notifications = $request->input('status');
        $user->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Approve or revoke an SSO account. Admin only, enforced by route middleware.
     *
     * Approval decides whether the account can sign in, so unlike the other
     * endpoints here it does act on the id supplied by the caller.
     */
    public function setSocialiteApprovalStatus($userid, Request $request)
    {
        $user = User::findOrFail($userid);
        $user->is_socialite_approved = $request->input('status');
        $user->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Set locale and date preferences for the authenticated user.
     *
     * The {userid} route segment is ignored, as with setNotificationStatus().
     */
    public function setLocale($userid, Request $request)
    {
        $user = Auth::user();
        $user->locale = $request->input('locale');
        $user->datestyle = $request->input('datestyle');
        $user->timestyle = $request->input('timestyle');
        $user->save();

        return response()->json(['status' => 'success']);
    }

    public function addExternalLink(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'required|string|max:255',
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
        $user = Auth::user();

        return response()->json($user->external_links);
    }

    /**
     * Change the authenticated user's own password.
     *
     * Self service, so the current password must be supplied. An Admin resetting
     * someone else's password does so through the user administration endpoints
     * and is not asked for the existing one. The {userid} route segment is
     * ignored: a caller cannot change an account other than their own here.
     */
    public function changePassword(Request $request, $userid)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => ['current_password' => ['The provided password does not match our records.']],
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['status' => 'success']);
    }
}
