<?php

namespace App\Services\Utilities;

/**
 * Thin wrapper over the name lookups used to decide where a device connection
 * really goes. DeviceTargetGuard resolves it from the container, so tests can
 * swap in a fixed map instead of depending on real DNS. Keep it free of policy:
 * it only reports addresses.
 */
class HostResolver
{
    private const HOSTS_FILE = '/etc/hosts';

    /**
     * IPv4 addresses for a name, through the system resolver. This covers
     * /etc/hosts, DNS and the shorthand IPv4 forms the C library accepts
     * (2130706433, 127.1, 0177.0.0.1), which is how fsockopen reads them.
     *
     * @return list<string>
     */
    public function ipv4Addresses(string $host): array
    {
        $addresses = @gethostbynamel($host);

        return $addresses === false ? [] : array_values(array_unique($addresses));
    }

    /**
     * IPv6 addresses for a name, from DNS AAAA records and /etc/hosts.
     *
     * @return list<string>
     */
    public function ipv6Addresses(string $host): array
    {
        $addresses = [];

        $records = @dns_get_record($host, DNS_AAAA);
        foreach (is_array($records) ? $records : [] as $record) {
            if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        foreach ($this->hostsFileIpv6Addresses($host) as $address) {
            $addresses[] = $address;
        }

        return array_values(array_unique($addresses));
    }

    /**
     * @return list<string>
     */
    private function hostsFileIpv6Addresses(string $host): array
    {
        $contents = is_readable(self::HOSTS_FILE) ? @file_get_contents(self::HOSTS_FILE) : false;

        if ($contents === false) {
            return [];
        }

        $addresses = [];

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $fields = preg_split('/\s+/', trim(explode('#', $line, 2)[0])) ?: [];

            if (count($fields) < 2 || filter_var($fields[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                continue;
            }

            if (in_array($host, array_map('strtolower', array_slice($fields, 1)), true)) {
                $addresses[] = $fields[0];
            }
        }

        return $addresses;
    }
}
