<?php

namespace App\Http\Middleware;

use App\Models\RestApiToken;
use App\Models\User;
use App\Traits\RespondsWithHttpStatus;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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

            if ($apitoken === null || ! in_array($apitoken, $this->activeTokens(), true)) {
                return $this->respondUnauthorized();
            }

            // The external API is token authenticated and has no per-token user. Only Admin
            // accounts can create tokens and every token has full Admin rights, so act as a
            // system user for the request so that downstream authorization (auth()->check())
            // and Auth::user() calls resolve.
            if ($systemUser = User::orderBy('id')->first()) {
                auth()->setUser($systemUser);
            }

            return $next($request);
        } catch (Exception $e) {
            return $this->failureResponse($e->getMessage(), 401);
        }
    }

    /**
     * The plaintext values of every token that may authenticate a request.
     *
     * A token with `api_token_status` 0 is disabled and is left out. A null status
     * counts as active, so tokens stored before the status was enforced keep working.
     *
     * @return array<int, string>
     */
    private function activeTokens(): array
    {
        return RestApiToken::query()
            ->where(function (Builder $query): void {
                $query->whereNull('api_token_status')->orWhere('api_token_status', '!=', 0);
            })
            ->pluck('api_token')
            ->filter(fn (mixed $token): bool => is_string($token) && $token !== '')
            ->values()
            ->all();
    }

    public function respondUnauthorized(): Response
    {
        return $this->failureResponse('Unauthorized', 401);
    }
}
