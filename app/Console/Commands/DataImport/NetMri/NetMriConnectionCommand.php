<?php

namespace App\Console\Commands\DataImport\NetMri;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class NetMriConnectionCommand extends Command
{
    protected $signature = 'rconfig:netmri-connection
                            {--setup : Setup NetMRI connection interactively}
                            {--test : Test existing connection}
                            {--show : Show current connection info}
                            {--edit-filters : Edit device filters}
                            {--set-url= : Update API URL}
                            {--clear : Clear connection info}
                            {--info : Display information about this command}';
    protected $description = 'Manage NetMRI API connection configuration';
    protected $connectionFile;
    protected $stubFile;

    public function __construct()
    {
        parent::__construct();
        $this->connectionFile = storage_path('app/rconfig/netmri_connection.json');
        $this->stubFile = storage_path('app/rconfig/netmri_connection.stub.json');
    }

    public function handle()
    {
        // Ensure directory exists
        $dir = storage_path('app/rconfig');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create stub file if it doesn't exist
        if (! File::exists($this->stubFile)) {
            $this->createStubFile();
        }

        // Handle command options
        if ($this->option('info')) {
            $this->showInfo();

            return 0;
        }

        if ($this->option('setup')) {
            return $this->setupConnection();
        }

        if ($this->option('test')) {
            return $this->testConnection();
        }

        if ($this->option('show')) {
            return $this->showConnection();
        }

        if ($this->option('edit-filters')) {
            return $this->editFilters();
        }

        if ($url = $this->option('set-url')) {
            return $this->setUrl($url);
        }

        if ($this->option('clear')) {
            return $this->clearConnection();
        }

        // Show interactive menu
        $this->showMenu();

        return 0;
    }

    protected function showMenu()
    {
        note('NetMRI Connection Management');

        $action = select(
            'What would you like to do?',
            [
                'info' => 'Show information about this command',
                'setup' => 'Setup NetMRI connection',
                'test' => 'Test existing connection',
                'show' => 'Show current connection info',
                'edit-filters' => 'Edit device filters',
                'clear' => 'Clear connection info',
                'exit' => 'Exit',
            ],
            'info'
        );

        switch ($action) {
            case 'info':
                $this->showInfo();
                break;
            case 'setup':
                $this->setupConnection();
                break;
            case 'test':
                $this->testConnection();
                break;
            case 'show':
                $this->showConnection();
                break;
            case 'edit-filters':
                $this->editFilters();
                break;
            case 'clear':
                $this->clearConnection();
                break;
            case 'exit':
                return;
        }

        if ($action !== 'exit' && confirm('Return to menu?')) {
            $this->showMenu();
        }
    }

    protected function setupConnection()
    {
        info('NetMRI Connection Setup');
        $this->line('');

        // Get API details
        $apiUrl = text(
            'NetMRI API URL (e.g., https://netmri.company.com):',
            required: true,
            validate: fn ($value) => filter_var($value, FILTER_VALIDATE_URL) ? null : 'Invalid URL format'
        );

        $apiUrl = rtrim($apiUrl, '/');

        $apiVersion = text(
            'API Version (default: 3.9):',
            default: '3.9'
        );

        $authType = select(
            'Authentication type:',
            ['basic' => 'Basic Auth (username/password)', 'token' => 'API Token'],
            'basic'
        );

        $username = null;
        $apiToken = null;
        $storePassword = false;
        $password = null;

        if ($authType === 'basic') {
            $username = text('Username:', required: true);
            $password = password('Password:', required: true);

            $storePassword = confirm('Store password in config file? (Not recommended for production)', false);
        } else {
            $apiToken = password('API Token:', required: true);
        }

        $verifySsl = confirm('Verify SSL certificates?', true);

        // Create connection config
        $config = [
            'api_url' => $apiUrl,
            'api_version' => $apiVersion,
            'auth_type' => $authType,
            'username' => $username,
            'password' => $storePassword ? base64_encode($password) : null,
            'password_encrypted' => $storePassword,
            'api_token' => $apiToken ? base64_encode($apiToken) : null,
            'verify_ssl' => $verifySsl,
            'timeout' => 30,
            'created_at' => Carbon::now()->toIso8601String(),
            'last_tested' => null,
            'connection_status' => 'untested',
            'netmri_version' => null,
            'device_count' => null,
            'filters' => [
                'include_sites' => [],
                'exclude_sites' => [],
                'include_zones' => [],
                'exclude_zones' => [],
                'include_device_types' => [],
                'exclude_device_types' => [],
                'only_managed' => true,
            ],
        ];

        // Test connection
        $this->line('');
        info('Testing connection to NetMRI...');

        $testResult = $this->performConnectionTest($config, $password ?: $apiToken);

        if ($testResult['success']) {
            $config['last_tested'] = Carbon::now()->toIso8601String();
            $config['connection_status'] = 'success';
            $config['netmri_version'] = $testResult['version'] ?? 'unknown';
            $config['device_count'] = $testResult['device_count'] ?? 0;

            File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

            info('✓ Connection successful!');
            note("✓ NetMRI version: {$config['netmri_version']}");
            note("✓ Found {$config['device_count']} devices");
            $this->line('');
            info('Connection info saved to: ' . basename($this->connectionFile));
            $this->line('');

            note('Next steps:');
            $this->line('1. Configure filters (optional): php artisan rconfig:netmri-connection --edit-filters');
            $this->line('2. Create device mappings: php artisan rconfig:netmri-device-mappings');
            $this->line('3. Load devices: php artisan rconfig:netmri-load-devices');

            return 0;
        } else {
            error('✗ Connection failed: ' . $testResult['error']);

            if (confirm('Save connection info anyway?', false)) {
                $config['connection_status'] = 'failed';
                File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));
                note('Connection info saved (marked as failed)');
            }

            return 1;
        }
    }

    protected function testConnection()
    {
        if (! File::exists($this->connectionFile)) {
            $this->line('No connection configuration found.');
            $this->line('Run: php artisan rconfig:netmri-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info("Testing connection to: {$config['api_url']}");

        // Get password if needed
        $credential = null;
        if ($config['auth_type'] === 'basic') {
            if ($config['password'] && $config['password_encrypted']) {
                $credential = base64_decode($config['password']);
            } else {
                $credential = password("Password for {$config['username']}:", required: true);
            }
        } else {
            $credential = base64_decode($config['api_token']);
        }

        $result = $this->performConnectionTest($config, $credential);

        if ($result['success']) {
            // Update connection status
            $config['last_tested'] = Carbon::now()->toIso8601String();
            $config['connection_status'] = 'success';
            $config['netmri_version'] = $result['version'] ?? 'unknown';
            $config['device_count'] = $result['device_count'] ?? 0;

            File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

            info('✓ Connection successful!');
            note("NetMRI version: {$config['netmri_version']}");
            note("Total devices: {$config['device_count']}");

            return 0;
        } else {
            error('✗ Connection failed: ' . $result['error']);

            return 1;
        }
    }

    protected function performConnectionTest($config, $credential)
    {
        try {
            $http = Http::timeout($config['timeout']);

            if (! $config['verify_ssl']) {
                $http = $http->withoutVerifying();
            }

            // Setup authentication
            if ($config['auth_type'] === 'basic') {
                $http = $http->withBasicAuth($config['username'], $credential);
            } else {
                $http = $http->withToken($credential);
            }

            // Test with a simple API call to get system info
            $response = $http->get("{$config['api_url']}/api/{$config['api_version']}/about");

            if ($response->successful()) {
                $data = $response->json();

                // Get device count
                $devicesResponse = $http->get("{$config['api_url']}/api/{$config['api_version']}/devices/index", [
                    'limit' => 1,
                ]);

                $deviceCount = 0;
                if ($devicesResponse->successful()) {
                    $devicesData = $devicesResponse->json();
                    $deviceCount = $devicesData['total'] ?? 0;
                }

                return [
                    'success' => true,
                    'version' => $data['version'] ?? 'unknown',
                    'device_count' => $deviceCount,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}: {$response->body()}",
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function showConnection()
    {
        if (! File::exists($this->connectionFile)) {
            $this->line('No connection configuration found.');
            $this->line('Run: php artisan rconfig:netmri-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info('Current NetMRI Connection Configuration:');
        $this->line('');

        note("API URL: {$config['api_url']}");
        note("API Version: {$config['api_version']}");
        note("Auth Type: {$config['auth_type']}");

        if ($config['auth_type'] === 'basic') {
            note("Username: {$config['username']}");
            note('Password: ' . ($config['password'] ? '********' : 'not stored (will prompt)'));
        } else {
            note('API Token: ' . ($config['api_token'] ? '********' : 'not stored'));
        }

        note('Verify SSL: ' . ($config['verify_ssl'] ? 'yes' : 'no'));
        note("Timeout: {$config['timeout']}s");
        $this->line('');

        note("Connection Status: {$config['connection_status']}");
        if ($config['last_tested']) {
            note("Last Tested: {$config['last_tested']}");
        }
        if ($config['netmri_version']) {
            note("NetMRI Version: {$config['netmri_version']}");
        }
        if ($config['device_count']) {
            note("Total Devices: {$config['device_count']}");
        }

        $this->line('');
        info('Active Filters:');

        $filters = $config['filters'];
        $hasFilters = false;

        if (! empty($filters['include_sites'])) {
            note('Include Sites: ' . implode(', ', $filters['include_sites']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_sites'])) {
            note('Exclude Sites: ' . implode(', ', $filters['exclude_sites']));
            $hasFilters = true;
        }
        if (! empty($filters['include_zones'])) {
            note('Include Zones: ' . implode(', ', $filters['include_zones']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_zones'])) {
            note('Exclude Zones: ' . implode(', ', $filters['exclude_zones']));
            $hasFilters = true;
        }
        if (! empty($filters['include_device_types'])) {
            note('Include Device Types: ' . implode(', ', $filters['include_device_types']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_device_types'])) {
            note('Exclude Device Types: ' . implode(', ', $filters['exclude_device_types']));
            $hasFilters = true;
        }
        if ($filters['only_managed']) {
            note('Only Managed Devices: yes');
            $hasFilters = true;
        }

        if (! $hasFilters) {
            note('No filters configured (will import all devices)');
        }

        return 0;
    }

    protected function editFilters()
    {
        if (! File::exists($this->connectionFile)) {
            $this->line('No connection configuration found.');
            $this->line('Run: php artisan rconfig:netmri-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info('NetMRI Device Filters');
        $this->line('');

        // Get credentials for API calls
        $credential = $this->getCredential($config);
        if (! $credential) {
            return 1;
        }

        // Fetch available sites, zones, and device types from NetMRI
        $metadata = $this->fetchNetMriMetadata($config, $credential);

        if (! $metadata['success']) {
            error('Failed to fetch NetMRI metadata: ' . $metadata['error']);

            return 1;
        }

        // Configure site filters
        if (! empty($metadata['sites'])) {
            $this->line('');
            info('Available Sites in NetMRI:');
            foreach ($metadata['sites'] as $site) {
                $this->line("  - {$site['name']} ({$site['device_count']} devices)");
            }

            $siteFilter = select(
                'Filter by sites?',
                [
                    'all' => 'Import all sites',
                    'include' => 'Include specific sites',
                    'exclude' => 'Exclude specific sites',
                ],
                'all'
            );

            if ($siteFilter === 'include') {
                $siteOptions = array_combine(
                    array_column($metadata['sites'], 'name'),
                    array_map(fn ($s) => "{$s['name']} ({$s['device_count']} devices)", $metadata['sites'])
                );
                $config['filters']['include_sites'] = multiselect(
                    'Select sites to include:',
                    $siteOptions
                );
                $config['filters']['exclude_sites'] = [];
            } elseif ($siteFilter === 'exclude') {
                $siteOptions = array_combine(
                    array_column($metadata['sites'], 'name'),
                    array_map(fn ($s) => "{$s['name']} ({$s['device_count']} devices)", $metadata['sites'])
                );
                $config['filters']['exclude_sites'] = multiselect(
                    'Select sites to exclude:',
                    $siteOptions
                );
                $config['filters']['include_sites'] = [];
            } else {
                $config['filters']['include_sites'] = [];
                $config['filters']['exclude_sites'] = [];
            }
        }

        // Configure zone filters
        if (! empty($metadata['zones'])) {
            $this->line('');
            info('Available Zones in NetMRI:');
            foreach ($metadata['zones'] as $zone) {
                $this->line("  - {$zone['name']} ({$zone['device_count']} devices)");
            }

            $zoneFilter = select(
                'Filter by zones?',
                [
                    'all' => 'Import all zones',
                    'include' => 'Include specific zones',
                    'exclude' => 'Exclude specific zones',
                ],
                'all'
            );

            if ($zoneFilter === 'include') {
                $zoneOptions = array_combine(
                    array_column($metadata['zones'], 'name'),
                    array_map(fn ($z) => "{$z['name']} ({$z['device_count']} devices)", $metadata['zones'])
                );
                $config['filters']['include_zones'] = multiselect(
                    'Select zones to include:',
                    $zoneOptions
                );
                $config['filters']['exclude_zones'] = [];
            } elseif ($zoneFilter === 'exclude') {
                $zoneOptions = array_combine(
                    array_column($metadata['zones'], 'name'),
                    array_map(fn ($z) => "{$z['name']} ({$z['device_count']} devices)", $metadata['zones'])
                );
                $config['filters']['exclude_zones'] = multiselect(
                    'Select zones to exclude:',
                    $zoneOptions
                );
                $config['filters']['include_zones'] = [];
            } else {
                $config['filters']['include_zones'] = [];
                $config['filters']['exclude_zones'] = [];
            }
        }

        // Configure device type filters
        if (! empty($metadata['device_types'])) {
            $this->line('');
            info('Available Device Types:');
            foreach ($metadata['device_types'] as $type) {
                $this->line("  - {$type['name']} ({$type['device_count']} devices)");
            }

            $typeFilter = select(
                'Filter by device types?',
                [
                    'all' => 'Import all device types',
                    'include' => 'Include specific types',
                    'exclude' => 'Exclude specific types',
                ],
                'all'
            );

            if ($typeFilter === 'include') {
                $typeOptions = array_combine(
                    array_column($metadata['device_types'], 'name'),
                    array_map(fn ($t) => "{$t['name']} ({$t['device_count']} devices)", $metadata['device_types'])
                );
                $config['filters']['include_device_types'] = multiselect(
                    'Select device types to include:',
                    $typeOptions
                );
                $config['filters']['exclude_device_types'] = [];
            } elseif ($typeFilter === 'exclude') {
                $typeOptions = array_combine(
                    array_column($metadata['device_types'], 'name'),
                    array_map(fn ($t) => "{$t['name']} ({$t['device_count']} devices)", $metadata['device_types'])
                );
                $config['filters']['exclude_device_types'] = multiselect(
                    'Select device types to exclude:',
                    $typeOptions
                );
                $config['filters']['include_device_types'] = [];
            } else {
                $config['filters']['include_device_types'] = [];
                $config['filters']['exclude_device_types'] = [];
            }
        }

        // Only managed devices?
        $this->line('');
        $config['filters']['only_managed'] = confirm('Only import managed devices?', true);

        // Save updated config
        File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

        $this->line('');
        info('Filters saved!');

        // Calculate estimated device count
        $estimatedCount = $this->estimateFilteredDeviceCount($metadata, $config['filters']);
        note("Estimated devices to import: {$estimatedCount['included']} ({$estimatedCount['excluded']} filtered out)");

        return 0;
    }

    protected function fetchNetMriMetadata($config, $credential)
    {
        try {
            $http = Http::timeout($config['timeout']);

            if (! $config['verify_ssl']) {
                $http = $http->withoutVerifying();
            }

            if ($config['auth_type'] === 'basic') {
                $http = $http->withBasicAuth($config['username'], $credential);
            } else {
                $http = $http->withToken($credential);
            }

            $metadata = [
                'success' => true,
                'sites' => [],
                'zones' => [],
                'device_types' => [],
            ];

            // Fetch sites
            $sitesResponse = $http->get("{$config['api_url']}/api/{$config['api_version']}/device_sites/index");
            if ($sitesResponse->successful()) {
                $sitesData = $sitesResponse->json();
                foreach ($sitesData['device_sites'] ?? [] as $site) {
                    $metadata['sites'][] = [
                        'name' => $site['DeviceSiteName'] ?? $site['name'] ?? 'Unknown',
                        'device_count' => $site['DeviceCount'] ?? 0,
                    ];
                }
            }

            // Fetch zones
            $zonesResponse = $http->get("{$config['api_url']}/api/{$config['api_version']}/device_zones/index");
            if ($zonesResponse->successful()) {
                $zonesData = $zonesResponse->json();
                foreach ($zonesData['device_zones'] ?? [] as $zone) {
                    $metadata['zones'][] = [
                        'name' => $zone['DeviceZoneName'] ?? $zone['name'] ?? 'Unknown',
                        'device_count' => $zone['DeviceCount'] ?? 0,
                    ];
                }
            }

            // Fetch device types
            $typesResponse = $http->get("{$config['api_url']}/api/{$config['api_version']}/device_types/index");
            if ($typesResponse->successful()) {
                $typesData = $typesResponse->json();
                foreach ($typesData['device_types'] ?? [] as $type) {
                    $metadata['device_types'][] = [
                        'name' => $type['DeviceType'] ?? $type['type'] ?? 'Unknown',
                        'device_count' => $type['DeviceCount'] ?? 0,
                    ];
                }
            }

            return $metadata;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function estimateFilteredDeviceCount($metadata, $filters)
    {
        $totalDevices = array_sum(array_column($metadata['device_types'], 'device_count'));
        $excludedCount = 0;

        // This is a rough estimation - actual filtering happens during device load
        // For now, just return total
        return [
            'included' => $totalDevices - $excludedCount,
            'excluded' => $excludedCount,
        ];
    }

    protected function setUrl($url)
    {
        if (! File::exists($this->connectionFile)) {
            $this->line('No connection configuration found.');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);
        $config['api_url'] = rtrim($url, '/');

        File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

        info("API URL updated to: {$url}");
        note('Run --test to verify the new URL');

        return 0;
    }

    protected function clearConnection()
    {
        if (! File::exists($this->connectionFile)) {
            $this->line('No connection configuration found.');

            return 0;
        }

        if (! confirm('Are you sure you want to clear the NetMRI connection configuration?', false)) {
            note('Operation cancelled.');

            return 0;
        }

        File::delete($this->connectionFile);
        $this->line('Connection configuration cleared.');

        return 0;
    }

    protected function getCredential($config)
    {
        if ($config['auth_type'] === 'basic') {
            if ($config['password'] && $config['password_encrypted']) {
                return base64_decode($config['password']);
            } else {
                return password("Password for {$config['username']}:", required: true);
            }
        } else {
            if ($config['api_token']) {
                return base64_decode($config['api_token']);
            } else {
                error('No API token found in configuration.');

                return null;
            }
        }
    }

    protected function createStubFile()
    {
        $stub = [
            '_comment' => 'NetMRI API Connection Configuration Template',
            '_instructions' => [
                '1. Copy this file to netmri_connection.json',
                '2. Fill in your NetMRI details',
                '3. Run: php artisan rconfig:netmri-connection --test',
                '4. Delete this stub file when done',
            ],
            'api_url' => 'https://your-netmri-server.company.com',
            'api_version' => '3.9',
            '_auth_options' => [
                '_note' => "Set auth_type to 'basic' or 'token'",
                '_basic_auth' => 'Uses username/password',
                '_token_auth' => 'Uses API token (recommended)',
            ],
            'auth_type' => 'basic',
            'username' => 'your_api_username',
            'password' => null,
            'password_encrypted' => false,
            'api_token' => null,
            'verify_ssl' => true,
            'timeout' => 30,
            '_filter_instructions' => [
                '_note' => 'Leave arrays empty [] to import all',
                '_include_vs_exclude' => 'Use include OR exclude, not both',
                '_examples' => [
                    'include_sites' => ['DC-East', 'DC-West'],
                    'exclude_zones' => ['Lab', 'Testing'],
                ],
            ],
            'filters' => [
                'include_sites' => [],
                'exclude_sites' => [],
                'include_zones' => [],
                'exclude_zones' => [],
                'include_device_types' => [],
                'exclude_device_types' => [],
                'only_managed' => true,
            ],
        ];

        File::put($this->stubFile, json_encode($stub, JSON_PRETTY_PRINT));
    }

    protected function showInfo()
    {
        $this->line('');
        info('===== NetMRI Connection Management =====');
        $this->line('');

        note('PURPOSE:');
        $this->line('This command manages the connection configuration for NetMRI API access.');
        $this->line('It stores connection details, tests connectivity, and manages device filters.');
        $this->line('');

        note('CONNECTION FILE:');
        $this->line('Connection info is stored in: ' . basename($this->connectionFile));
        $this->line('Template file available at: ' . basename($this->stubFile));
        $this->line('');

        note('AUTHENTICATION:');
        $this->line('NetMRI supports two authentication methods:');
        $this->line('  - Basic Auth: Username and password');
        $this->line('  - Token Auth: API token (recommended for security)');
        $this->line('');
        $this->line('Passwords can be stored (encrypted) or prompted for each use.');
        $this->line('For production, prompting is more secure.');
        $this->line('');

        note('FILTERS:');
        $this->line('Device filters allow you to selectively import devices based on:');
        $this->line('  - Sites: Import/exclude specific data centers or locations');
        $this->line('  - Zones: Import/exclude specific network zones (Core, DMZ, etc.)');
        $this->line('  - Device Types: Import/exclude specific device types');
        $this->line('  - Management Status: Import only managed devices');
        $this->line('');

        note('USAGE:');
        $this->line('php artisan rconfig:netmri-connection --setup           Setup new connection');
        $this->line('php artisan rconfig:netmri-connection --test            Test existing connection');
        $this->line('php artisan rconfig:netmri-connection --show            Show current config');
        $this->line('php artisan rconfig:netmri-connection --edit-filters    Configure device filters');
        $this->line('php artisan rconfig:netmri-connection --set-url=URL     Update API URL');
        $this->line('php artisan rconfig:netmri-connection --clear           Clear configuration');
        $this->line('');

        note('WORKFLOW:');
        $this->line('1. Setup connection:     php artisan rconfig:netmri-connection --setup');
        $this->line('2. Configure filters:    php artisan rconfig:netmri-connection --edit-filters');
        $this->line('3. Create mappings:      php artisan rconfig:netmri-device-mappings');
        $this->line('4. Load devices:         php artisan rconfig:netmri-load-devices');
        $this->line('5. Import devices:       php artisan rconfig:netmri-import-devices');
        $this->line('');

        note('RELATED COMMANDS:');
        $this->line('rconfig:netmri-connection       - Manage NetMRI API connection');
        $this->line('rconfig:netmri-device-mappings  - Manage device type mappings');
        $this->line('rconfig:netmri-load-devices     - Load devices from NetMRI');
        $this->line('rconfig:netmri-import-devices   - Import devices to rConfig');
    }
}
