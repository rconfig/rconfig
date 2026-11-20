<?php

namespace App\Http\Controllers;

use App\Services\Security\Probes\PhpRuntimeInspector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The web server's php.ini values for rconfig:security-check. Signed, short-lived, loopback-only and allow-listed.
 */
class SecurityPhpRuntimeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! in_array((string) $request->server('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)) {
            abort(404);
        }

        $snapshot = PhpRuntimeInspector::local('web');

        return response()->json([
            'sapi' => $snapshot['sapi'],
            'version' => $snapshot['version'],
            'values' => $snapshot['values'],
        ])->header('Cache-Control', 'no-store');
    }
}
