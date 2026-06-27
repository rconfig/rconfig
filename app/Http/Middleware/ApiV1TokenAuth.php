<?php

namespace App\Http\Middleware;

use App\Models\RestApiToken;
use App\Models\User;
use App\Traits\RespondsWithHttpStatus;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiV1TokenAuth
{
    use RespondsWithHttpStatus;

    /**
     * Authenticate external REST API requests using the `apitoken` header or query parameter.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json'); // ForceJsonResponse

        try {
            if ($request->has('api_token')) {
                return $this->failureResponse('Token param name needs to be updated to apitoken.', 422);
            }

            if ($request->hasHeader('apitoken') || $request->has('apitoken')) {
                $apitoken = $request->hasHeader('apitoken') ? $request->header('apitoken') : $request->apitoken;
            } else {
                return $this->respondUnauthorized();
            }

            if ($apitoken === null || ! in_array($apitoken, RestApiToken::pluck('api_token')->toArray())) {
                return $this->respondUnauthorized();
            }

            // The external API is token authenticated and has no per-token user. Core grants
            // all authenticated users equal access, so act as a system user for the request so
            // that downstream authorization (auth()->check()) and Auth::user() calls resolve.
            if ($systemUser = User::orderBy('id')->first()) {
                auth()->setUser($systemUser);
            }

            return $next($request);
        } catch (Exception $e) {
            return $this->failureResponse($e->getMessage(), 401);
        }
    }

    public function respondUnauthorized(): Response
    {
        return $this->failureResponse('Unauthorized', 401);
    }
}
