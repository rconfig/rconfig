<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\DeviceCredentialsController as BaseDeviceCredentialsController;
use App\Traits\MaskableCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Device Credentials
 *
 * @authenticated
 */
class DeviceCredentialsController extends BaseDeviceCredentialsController
{
    use MaskableCredentials;

    /**
     * Secret fields that must never leave the external REST API in cleartext.
     *
     * @var array<int, string>
     */
    private const MASKED_FIELDS = ['cred_password', 'cred_enable_password'];

    /**
     * List credential sets with their secrets masked.
     *
     * Masking is unconditional here. The MASK_DEVICE_CREDENTIALS setting governs the
     * device endpoints only and must not be able to re-enable cleartext secrets on the
     * token authenticated API.
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null): JsonResponse
    {
        $credentials = $this->paginateCredentials($request)
            ->through(fn ($credential): array => $this->maskSecrets($credential->toArray()));

        return response()->json($credentials);
    }

    /**
     * Show a single credential set with its secrets masked.
     *
     * @return array<string, mixed>
     */
    public function show($id, $relationship = null, $withCount = null): array
    {
        return $this->maskSecrets(parent::show($id)->toArray());
    }

    /**
     * Replace secret values with a masked form, leaving blank secrets untouched.
     *
     * Blank-like values are left as they are so callers can still tell a credential set
     * has no enable password, rather than seeing stars that imply one exists.
     *
     * @param  array<string, mixed>  $credential
     * @return array<string, mixed>
     */
    private function maskSecrets(array $credential): array
    {
        foreach (self::MASKED_FIELDS as $field) {
            $value = $credential[$field] ?? null;

            if (is_string($value) && $value !== '') {
                $credential[$field] = $this->mask($value);
            }
        }

        return $credential;
    }
}
