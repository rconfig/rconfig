<?php

namespace App\Rules;

use App\Models\DeviceCredentials;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DeviceCredIdIsValid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === 0) {
            return;
        }

        $result = DeviceCredentials::where('id', $value)->first();

        if ($result === null) {
            $fail('Unable to locate this set of device credentials. Check this "device_cred_id" parameter and please try again.');
        }
    }
}
