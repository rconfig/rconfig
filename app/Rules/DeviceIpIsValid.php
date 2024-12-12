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

        // Check if it's a valid IP address or hostname/FQDN
        if (!$this->isValidIpAddress($value) && !$this->isValidHostname($value)) {
            $fail('The :attribute must be a valid IP address or FQDN/Hostname.');
        }
    }

    /**
     * Validate if the given value is a valid IP address.
     */
    private function isValidIpAddress(string $value): bool
    {
        // Validate both IPv4 and IPv6
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false
            || filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Validate if the given value is a valid hostname/FQDN.
     */
    private function isValidHostname(string $value): bool
    {
        // Use FILTER_VALIDATE_DOMAIN with FILTER_FLAG_HOSTNAME for hostname validation
        return filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}
