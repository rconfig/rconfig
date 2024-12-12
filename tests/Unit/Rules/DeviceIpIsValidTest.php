<?php

namespace Tests\Unit\Rules;

use App\Rules\DeviceIpIsValid;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DeviceIpIsValidTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function makeValidator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        $rule = ['ip_address' => [new DeviceIpIsValid]];
        return Validator::make($data, $rule);
    }

    public function test_it_allows_valid_ipv4_address()
    {
        $validator = $this->makeValidator(['ip_address' => '192.168.1.1']);
        $this->assertTrue($validator->passes());
    }

    public function test_it_allows_valid_ipv6_address()
    {
        $validator = $this->makeValidator(['ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);
        $this->assertTrue($validator->passes());
    }

    public function test_it_allows_valid_fqdn()
    {
        $validator = $this->makeValidator(['ip_address' => 'example.com']);
        $this->assertTrue($validator->passes());
    }

    public function test_it_fails_for_invalid_fqdn()
    {
        $validator = $this->makeValidator(['ip_address' => 'invalid_domain..com']);
        $this->assertFalse($validator->passes());
        $this->assertEquals(
            'The ip address must be a valid IP address or FQDN/Hostname.',
            $validator->errors()->first('ip_address')
        );
    }

    public function test_it_fails_for_null_value()
    {
        $validator = $this->makeValidator(['ip_address' => null]);
        $this->assertFalse($validator->passes());
        $this->assertEquals(
            'The ip address is required.',
            $validator->errors()->first('ip_address')
        );
    }

    public function test_it_allows_ipv4_mapped_ipv6_address()
    {
        $validator = $this->makeValidator(['ip_address' => '::ffff:192.0.2.128']);
        $this->assertTrue($validator->passes());
    }

    public function test_it_allows_mixed_case_fqdn()
    {
        $validator = $this->makeValidator(['ip_address' => 'ExAmPle.CoM']);
        $this->assertTrue($validator->passes());
    }
}
