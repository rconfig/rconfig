<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Utilities\VersionCheckService;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    use RespondsWithHttpStatus;

    public function __construct(
        private VersionCheckService $versionCheck
    ) {}

    /**
     * Return the current version status compared against the latest Core
     * release tag on GitHub.
     *
     * By default this reads the status last persisted by the scheduled
     * background job (a fast, non-blocking cache read). Pass ?clearCache=true
     * (the "Recheck" action) to perform a live lookup synchronously.
     */
    public function check(Request $request)
    {
        $status = $request->boolean('clearCache')
            ? $this->versionCheck->refresh()
            : $this->versionCheck->getStatus();

        return $this->successResponse('Version status retrieved', $status);
    }
}
