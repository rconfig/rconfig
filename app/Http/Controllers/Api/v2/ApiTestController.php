<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @group API Test
 *
 * @authenticated
 *
 * Simple connectivity check for the v2 REST API.
 */
class ApiTestController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to the rConfig REST API version 2',
        ], 200);
    }
}
