<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds the current request host to Sanctum's stateful domain list so the
 * first-party SPA authenticates via session cookie even when APP_URL or
 * SANCTUM_STATEFUL_DOMAINS are misconfigured. Cross-origin requests are
 * unaffected because the Referer host still has to match this server.
 */
class EnsureSameOriginIsStateful
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHttpHost();
        $stateful = config('sanctum.stateful', []);

        if (! in_array($host, $stateful, true)) {
            $stateful[] = $host;
            config(['sanctum.stateful' => $stateful]);
        }

        return $next($request);
    }
}
