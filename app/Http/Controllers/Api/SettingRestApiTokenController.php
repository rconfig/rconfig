<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestApiTokenRequest;
use App\Models\RestApiToken;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SettingRestApiTokenController extends Controller
{
    use RespondsWithHttpStatus;

    /**
     * List existing tokens. The raw token value is never returned here; it is only
     * shown once at creation time.
     */
    public function index(): JsonResponse
    {
        $tokens = RestApiToken::query()
            ->select(['id', 'api_token_name', 'api_token_status', 'created_at', 'updated_at'])
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse('Success', $tokens);
    }

    /**
     * Create a new token and return the plaintext value once.
     */
    public function store(StoreRestApiTokenRequest $request): JsonResponse
    {
        $token = (string) Str::uuid();

        $model = RestApiToken::create([
            'api_token' => $token,
            'api_token_name' => $request->validated('api_token_name'),
            'api_token_status' => 1,
        ]);

        return $this->successResponse('API token created successfully!', [
            'id' => $model->id,
            'api_token_name' => $model->api_token_name,
            'api_token' => $token,
            'created_at' => $model->created_at,
        ]);
    }

    /**
     * Revoke a token.
     */
    public function destroy(int $id): JsonResponse
    {
        $model = RestApiToken::find($id);

        if (! $model) {
            return $this->failureResponse('API token not found.', 404);
        }

        $model->delete();

        return $this->successResponse('API token deleted successfully!', $id);
    }
}
