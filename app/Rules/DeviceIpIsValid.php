<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DeviceIpIsValid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ensure the value is not null or empty
        if (is_null($value) || trim($value) === '') {
            $fail('The :attribute is required.');
            return;
        }

        // Trim the value for validation
        $value = trim($value);

        // Check if the value is a valid IP address or a valid FQDN/Hostname
        if (!filter_var($value, FILTER_VALIDATE_IP) && !filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            $fail('The :attribute must be a valid IP address or FQDN/Hostname.');
        }
    }
}
