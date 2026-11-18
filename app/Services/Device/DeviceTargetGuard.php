<?php

namespace App\Services\Device;

use App\Exceptions\DeviceTargetRefusedException;
use App\Services\Utilities\HostResolver;

/**
 * Decides whether a device address points at this rConfig server. A device is
 * something rConfig connects out to, so a device on loopback lets device editors
 * make rConfig write into its own Redis (RCO-1813).
 *
 * Names are resolved, not matched as text: a DNS name, an /etc/hosts entry or a
 * shorthand IPv4 form (127.1, 2130706433) reaches loopback just as well as
 * 127.0.0.1 does (GHSA-p5q7-2wwp-q42f). The connectors dial the address returned
 * by dialHost(), never the name, so the address checked is the address dialled.
 */
class DeviceTargetGuard
{
    public const REFUSED_MESSAGE = 'points at this rConfig server (loopback, localhost or its Redis host)';

    public function __construct(private HostResolver $resolver) {}

    /**
     * True when the target is, or resolves to, this server. A name that does not
     * resolve is not refused here: it cannot be dialled either, and dialHost()
     * fails closed on it at connection time.
     */
    public function pointsAtThisServer(string $target): bool
    {
        $host = $this->normalise($target);

        if ($this->isThisServerName($host)) {
            return true;
        }

        return $this->anyAddressIsThisServer($this->addressesFor($host));
    }

    /**
     * The host string to hand to fsockopen or phpseclib for a device: a resolved
     * IP literal, bracketed when IPv6. IPv4 is preferred, matching what most
     * network gear is reached on.
     *
     * @throws DeviceTargetRefusedException when the target resolves to this server or to nothing
     */
    public function dialHost(string $target): string
    {
        $host = $this->normalise($target);
        $addresses = $this->isThisServerName($host) ? [] : $this->addressesFor($host);

        if ($this->isThisServerName($host) || $this->anyAddressIsThisServer($addresses)) {
            throw new DeviceTargetRefusedException("Refused to connect to {$target}: it " . self::REFUSED_MESSAGE . '.');
        }

        if ($addresses === []) {
            throw new DeviceTargetRefusedException("Unable to connect to {$target}: the name could not be resolved.");
        }

        usort($addresses, fn (string $a, string $b): int => $this->isIpv6($a) <=> $this->isIpv6($b));

        return $this->isIpv6($addresses[0]) ? '[' . $addresses[0] . ']' : $addresses[0];
    }

    private function normalise(string $target): string
    {
        $host = strtolower(trim($target));

        // A bracketed IPv6 literal ([::1]) is how a URL or fsockopen spells it.
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $host = substr($host, 1, -1);
        }

        return rtrim($host, '.');
    }

    private function isThisServerName(string $host): bool
    {
        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        $redisHost = $this->normalise((string) config('database.redis.default.host'));

        return $redisHost !== '' && $host === $redisHost;
    }

    /**
     * @return list<string>
     */
    private function addressesFor(string $host): array
    {
        if ($host === '') {
            return [];
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        return array_values(array_unique([
            ...$this->resolver->ipv4Addresses($host),
            ...$this->resolver->ipv6Addresses($host),
        ]));
    }

    /**
     * @param  list<string>  $addresses
     */
    private function anyAddressIsThisServer(array $addresses): bool
    {
        $redisAddresses = null;

        foreach ($addresses as $address) {
            if ($this->isLoopbackOrUnspecified($address)) {
                return true;
            }

            $redisAddresses ??= $this->redisAddresses();
            if (in_array($this->canonical($address), $redisAddresses, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The configured Redis host's addresses, so a different name or literal for the
     * same box (the Docker "redis" service, say) is refused too.
     *
     * @return list<string>
     */
    private function redisAddresses(): array
    {
        $redisHost = $this->normalise((string) config('database.redis.default.host'));

        return array_values(array_filter(array_map(
            fn (string $address): ?string => $this->canonical($address),
            $this->addressesFor($redisHost),
        )));
    }

    private function isLoopbackOrUnspecified(string $address): bool
    {
        $packed = $this->packedIpv4OrIpv6($address);

        if ($packed === null) {
            return false;
        }

        if (strlen($packed) === 4) {
            // 127.0.0.0/8 is loopback, and Linux routes a connect to 0.0.0.0 to the local host.
            return $packed[0] === "\x7f" || $packed[0] === "\0";
        }

        return $packed === inet_pton('::1') || $packed === inet_pton('::');
    }

    /**
     * Binary form, with IPv4-mapped IPv6 (::ffff:127.0.0.1) unwrapped to the IPv4
     * address it reaches.
     */
    private function packedIpv4OrIpv6(string $address): ?string
    {
        $packed = @inet_pton($address);

        if ($packed === false) {
            return null;
        }

        if (strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10) . "\xff\xff")) {
            return substr($packed, 12);
        }

        return $packed;
    }

    private function canonical(string $address): ?string
    {
        $packed = $this->packedIpv4OrIpv6($address);

        return $packed === null ? null : inet_ntop($packed);
    }

    private function isIpv6(string $address): bool
    {
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }
}
