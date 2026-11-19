<?php

namespace Tests\Traits;

use App\Services\Utilities\HostResolver;

/**
 * Swaps the HostResolver DNS seam for a fixed map, so device target checks are
 * tested without real DNS. A name missing from the map resolves to nothing.
 */
trait FakesHostResolver
{
    /**
     * @param  array<string, list<string>>  $addressesByName  lowercase name => addresses (IPv4 and IPv6 mixed)
     */
    protected function fakeHostResolver(array $addressesByName = []): HostResolver
    {
        $fake = new class($addressesByName) extends HostResolver
        {
            /** @var list<string> names looked up, in order */
            public array $lookups = [];

            /**
             * @param  array<string, list<string>>  $addressesByName
             */
            public function __construct(private array $addressesByName) {}

            public function ipv4Addresses(string $host): array
            {
                $this->lookups[] = $host;

                return array_values(array_filter(
                    $this->addressesByName[$host] ?? [],
                    fn (string $address): bool => filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false,
                ));
            }

            public function ipv6Addresses(string $host): array
            {
                return array_values(array_filter(
                    $this->addressesByName[$host] ?? [],
                    fn (string $address): bool => filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false,
                ));
            }
        };

        app()->instance(HostResolver::class, $fake);

        return $fake;
    }
}
