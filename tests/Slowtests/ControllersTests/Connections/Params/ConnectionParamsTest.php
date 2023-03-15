<?php

namespace Tests\Slowtests\ControllersTests\Connections\Params;

use App\Http\Controllers\Connections\Params\ConnectionParams;
use App\Models\Device;
use Tests\TestCase;

class ConnectionParamsTest extends TestCase
{
    protected $user;

    protected $device;

    public function setUp(): void
    {
        parent::setUp();
        $this->device = Device::where('id', 1001)->first();
    }

    /** @test */
    public function can_get_a_template_for_this_device()
    {
        $template = new ConnectionParams($this->device->device_template);
        $result = $template->getTemplateParams();

        $this->assertStringContainsString($result['main']['name'], 'Cisco IOS - TELNET - No Enable');
        $this->assertStringContainsString($result['connect']['protocol'], 'telnet');
    }
}
