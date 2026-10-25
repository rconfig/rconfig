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
        if (! $this->isValidIpAddress($value) && ! $this->isValidHostname($value)) {
            $fail('The :attribute must be a valid IP address or FQDN/Hostname.');

            return;
        }

        if ($this->pointsAtThisServer($value)) {
            $fail('The :attribute cannot point at this rConfig server (loopback, localhost or its Redis host).');
        }
    }

    /**
     * A device is something rConfig connects out to from its own server, so a device on
     * loopback lets device editors make rConfig write into its own Redis. See RCO-1813.
     */
    private function pointsAtThisServer(string $value): bool
    {
        $host = strtolower(rtrim($value, '.'));

        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        if ($host === strtolower((string) config('database.redis.default.host'))) {
            return true;
        }

        $packed = @inet_pton($host);

        if ($packed === false) {
            return false;
        }

        // IPv4-mapped IPv6 (::ffff:127.0.0.1) reaches the same IPv4 address.
        if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10) . "\xff\xff")) {
            $packed = substr($packed, 12);
        }

        if (strlen($packed) === 4) {
            // 127.0.0.0/8 is loopback, and Linux routes a connect to 0.0.0.0 to the local host.
            return $packed[0] === "\x7f" || $packed === "\0\0\0\0";
        }

        return $packed === inet_pton('::1') || $packed === inet_pton('::');
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
