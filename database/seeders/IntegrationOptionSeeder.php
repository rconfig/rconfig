<?php

namespace Database\Seeders;

use App\Models\IntegrationOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntegrationOptionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('integration_options')->truncate();

        $options = [
            [
                'id' => 1,
                'icon' => 'MSLogo',
                'name' => 'Microsoft SSO',
                'type' => 'SSO',
                'description' => 'Single sign-on with Microsoft',
                'action_text' => 'Configure',
                'config_url' => 'https://docs.rconfig.com/integrations/sso/sso-ms/',
                'external_url' => true,
                'status' => 'Active',
            ],
            [
                'id' => 2,
                'icon' => 'OktaLogo',
                'name' => 'Okta SSO',
                'type' => 'SSO',
                'description' => 'Single sign-on with Okta',
                'action_text' => 'Configure',
                'config_url' => 'https://docs.rconfig.com/integrations/sso/sso-okta/',
                'external_url' => true,
                'status' => 'Active',
            ],
            [
                'id' => 3,
                'icon' => 'PassboltLogo',
                'name' => 'Passbolt',
                'type' => 'Device Credentials',
                'description' => 'Use Passbolt for device credentials',
                'action_text' => 'Request',
                'config_url' => 'https://rconfig.com/support-center',
                'external_url' => true,
                'status' => 'Disabled',
            ],
            [
                'id' => 5,
                'icon' => 'GoogleLogo',
                'name' => 'Google SSO',
                'type' => 'SSO',
                'description' => 'Single sign-on with Google',
                'action_text' => 'Configure',
                'config_url' => 'https://docs.rconfig.com/integrations/sso/sso-google/',
                'external_url' => true,
                'status' => 'Active',
            ],
        ];

        IntegrationOption::truncate();
        IntegrationOption::insert($options);
    }
}
