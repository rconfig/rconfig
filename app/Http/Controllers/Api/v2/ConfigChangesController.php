<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\ConfigChange;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\JsonResponse;

/**
 * @group Config Changes
 *
 * @authenticated
 */
class ConfigChangesController extends Controller
{
    use RespondsWithHttpStatus;

    /**
     * Return the change record (including the rendered diff) for a given config id.
     */
    public function byConfig(int $config): JsonResponse
    {
        $change = ConfigChange::where('current_config_id', $config)->first();

        if (! $change) {
            return $this->failureResponse('No change record found for this config.', 404);
        }

        return $this->successResponse('Success', $change);
    }

    /**
     * Show a single config change by its own id.
     */
    public function show(int $id): JsonResponse
    {
        $change = ConfigChange::find($id);

        if (! $change) {
            return $this->failureResponse('Config change not found.', 404);
        }

        return $this->successResponse('Success', $change);
    }
}
