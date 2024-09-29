<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Tests\TestCase;

class SettingsSupportInfoControllerTest extends TestCase
{
    protected $user;

    protected $setting;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

     public function test_get_support_info()
    {
        $response = $this->get('/api/settings/support-info');
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure(
            [
                'success',
                'data' => [
                    'version',
                    'rconfig_sub_id',
                    'rconfig_sub_name',
                    'rconfig_sub_status',
                    'rconfig_sub_expiry',
                ],
            ]
        );
    }
}
