<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use Tests\Unit\Connections\DeviceParamsBuilder;

/**
 * Covers Connect::applyKexAlgorithmOverride(), the opt-in template setting that restricts
 * SSH key exchange for vendor sshd builds that hang negotiating phpseclib's default
 * algorithm preferences. Ported from Pro RCO-1191, see RCO-1588.
 */
const KEX_TEST_ALGORITHMS = ['diffie-hellman-group14-sha256'];

/**
 * Builds a Connect for the given kex keys, applies the override and returns every
 * setPreferredAlgorithms() call it made.
 *
 * @return list<array<string, mixed>>
 */
function kexPreferredAlgorithmCalls(mixed $kexOverride, mixed $kexAlgorithms): array
{
    $connect = new SshConnect(DeviceParamsBuilder::forSsh([
        'connect' => ['kexOverride' => $kexOverride, 'kexAlgorithms' => $kexAlgorithms],
    ]), false);

    $connect->connection = new class
    {
        /** @var list<array<string, mixed>> */
        public array $calls = [];

        /**
         * @param  array<string, mixed>  $methods
         */
        public function setPreferredAlgorithms(array $methods): void
        {
            $this->calls[] = $methods;
        }
    };

    (new ReflectionMethod(SshConnect::class, 'applyKexAlgorithmOverride'))->invoke($connect);

    return $connect->connection->calls;
}

test('preferred algorithms are never set when the override is disabled', function (mixed $kexOverride, mixed $kexAlgorithms) {
    expect(kexPreferredAlgorithmCalls($kexOverride, $kexAlgorithms))->toBe([]);
})->with([
    'flag absent' => [null, KEX_TEST_ALGORITHMS],
    'flag off' => ['off', KEX_TEST_ALGORITHMS],
    'flag on but algorithms empty' => ['on', []],
    'flag on but algorithms absent' => ['on', null],
    'flag on but algorithms not a list' => ['on', 'diffie-hellman-group14-sha256'],
]);

test('preferred algorithms are set for every tolerant truthy spelling', function (mixed $kexOverride) {
    expect(kexPreferredAlgorithmCalls($kexOverride, KEX_TEST_ALGORITHMS))->toBe([['kex' => KEX_TEST_ALGORITHMS]]);
})->with([
    "'on'" => ['on'],
    "uppercase 'ON'" => ['ON'],
    "'yes'" => ['yes'],
    "'true'" => ['true'],
    'boolean true' => [true],
    'integer 1' => [1],
]);

test('the exact ordered algorithm list is passed through', function () {
    $algorithms = ['diffie-hellman-group1-sha1', 'diffie-hellman-group14-sha1'];

    expect(kexPreferredAlgorithmCalls('on', $algorithms))->toBe([['kex' => $algorithms]]);
});

test('connect builds without warnings when a template omits the kex keys', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(), false);

    expect($connect->kexOverride)->toBeNull();
    expect($connect->kexAlgorithms)->toBeNull();
});
