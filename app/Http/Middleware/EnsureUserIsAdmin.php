<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to users holding the Admin role.
 *
 * User administration is an Admin only surface. Account holders manage their own
 * details through the dedicated profile endpoints instead, which act on the
 * authenticated user rather than on an arbitrary id.
 */
class EnsureUserIsAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'This action requires an administrator account.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
