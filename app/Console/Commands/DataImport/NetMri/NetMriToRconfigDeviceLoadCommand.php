<?php

namespace App\Console\Commands\DataImport\NetMri;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\password;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\warning;

class NetMriToRconfigDeviceLoadCommand extends Command
{
    protected $signature = 'rconfig:netmri-load-devices
                            {--batch-size=100 : Number of devices to fetch per API call}
                            {--max-devices= : Maximum number of devices to import (for testing)}
                            {--info : Display information about this command}';
    protected $description = 'Load devices from NetMRI API to create rConfig import JSON';
    protected $connectionFile;
    protected $mappingsFile;

    public function __construct()
    {
        parent::__construct();
        $this->connectionFile = storage_path('app/rconfig/netmri_connection.json');
        $this->mappingsFile = storage_path('app/rconfig/netmri_mappings.json');
    }

    public function handle()
    {
        if ($this->option('info')) {
            $this->showInfo();

            return 0;
        }

        return $this->importFromNetMri();
    }

    protected function importFromNetMri()
    {
        // Check for connection file
        if (! File::exists($this->connectionFile)) {
            error('NetMRI connection not configured!');
            note('Run: php artisan rconfig:netmri-connection --setup');

            return 1;
        }

        // Check for mappings file
        if (! File::exists($this->mappingsFile)) {
            error('Device type mappings file not found!');
            note('Run: php artisan rconfig:netmri-device-mappings --add');

            return 1;
        }

        // Load connection and mappings
        $connection = json_decode(File::get($this->connectionFile), true);
        $mappings = json_decode(File::get($this->mappingsFile), true);

        if (empty($mappings)) {
            error('No device type mappings configured!');
            note('Run: php artisan rconfig:netmri-device-mappings --add');

            return 1;
        }

        info('Loading NetMRI connection info...');
        note("✓ Found connection to: {$connection['api_url']}");

        // Get credential
        $credential = null;
        if ($connection['auth_type'] === 'basic') {
            if ($connection['password'] && $connection['password_encrypted']) {
                $credential = base64_decode($connection['password']);
            } else {
                $credential = password("Password for {$connection['username']}:", required: true);
            }
        } else {
            if ($connection['api_token']) {
                $credential = base64_decode($connection['api_token']);
            } else {
                error('No API token found in configuration.');

                return 1;
            }
        }

        // Test connection
        info('Connecting to NetMRI...');
        $testResult = $this->testConnection($connection, $credential);

        if (! $testResult['success']) {
            error('✗ Connection failed: ' . $testResult['error']);

            return 1;
        }

        note('✓ Connected successfully');

        // Display active filters
        $filters = $connection['filters'];
        $this->displayActiveFilters($filters);

        if (! confirm('Proceed with device loading?')) {
            note('Operation cancelled.');

            return 0;
        }

        // Fetch devices from NetMRI
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $batchSize = $this->option('batch-size') ?? 100;
        $maxDevices = $this->option('max-devices');

        info('Fetching devices from NetMRI...');

        $devices = $this->fetchDevicesFromNetMri($connection, $credential, $filters, $batchSize, $maxDevices);

        if (empty($devices)) {
            warning('No devices retrieved from NetMRI.');

            return 1;
        }

        info("Retrieved {$devices['total']} devices from NetMRI");

        // Process devices
        $validDevices = [];
        $failures = [];

        $bar = progress('Processing devices', count($devices['devices']));

        foreach ($devices['devices'] as $device) {
            $deviceType = $device['DeviceType'] ?? $device['device_type'] ?? null;

            // Check if we have a mapping for this device type
            if (! $deviceType || ! isset($mappings[$deviceType])) {
                $failures[] = $device['DeviceName'] . " # Unknown device type '$deviceType'. No mapping found.";
                $bar->advance();
                continue;
            }

            // Process device
            try {
                $processedDevice = $this->processDevice($device, $mappings[$deviceType]);
                $validDevices[] = $processedDevice;
            } catch (\Exception $e) {
                $failures[] = $device['DeviceName'] . ' # Error: ' . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();

        // Save output files
        $outputFile = storage_path("app/rconfig/tempdir/rconfig_import_{$timestamp}.json");
        $failuresFile = storage_path("app/rconfig/tempdir/netmri_import_failures_{$timestamp}.txt");

        $dir = dirname($outputFile);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($outputFile, json_encode($validDevices, JSON_PRETTY_PRINT));

        if (! empty($failures)) {
            File::put($failuresFile, implode("\n", $failures));
            warning(count($failures) . ' devices failed processing. See ' . basename($failuresFile));
        }

        info('Device loading completed successfully!');
        note('Generated rConfig compatible data: ' . basename($outputFile));
        note('Valid devices processed: ' . count($validDevices));

        if (confirm('Would you like to view a summary?')) {
            $this->displaySummary($validDevices);
        }

        if (confirm('Would you like to import these devices into rConfig now?')) {
            $this->call('rconfig:netmri-import-devices', ['file' => $outputFile]);
        }

        return 0;
    }

    protected function testConnection($config, $credential)
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

            $response = $http->get("{$config['api_url']}/api/{$config['api_version']}/about");

            if ($response->successful()) {
                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}",
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function displayActiveFilters($filters)
    {
        $hasFilters = false;

        info('Active filters:');

        if (! empty($filters['include_sites'])) {
            note('  Include Sites: ' . implode(', ', $filters['include_sites']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_sites'])) {
            note('  Exclude Sites: ' . implode(', ', $filters['exclude_sites']));
            $hasFilters = true;
        }
        if (! empty($filters['include_zones'])) {
            note('  Include Zones: ' . implode(', ', $filters['include_zones']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_zones'])) {
            note('  Exclude Zones: ' . implode(', ', $filters['exclude_zones']));
            $hasFilters = true;
        }
        if (! empty($filters['include_device_types'])) {
            note('  Include Device Types: ' . implode(', ', $filters['include_device_types']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_device_types'])) {
            note('  Exclude Device Types: ' . implode(', ', $filters['exclude_device_types']));
            $hasFilters = true;
        }
        if ($filters['only_managed']) {
            note('  Only Managed Devices: yes');
            $hasFilters = true;
        }

        if (! $hasFilters) {
            note('  No filters active (will import all devices)');
        }

        $this->line('');
    }

    protected function fetchDevicesFromNetMri($config, $credential, $filters, $batchSize, $maxDevices)
    {
        $http = Http::timeout($config['timeout']);

        if (! $config['verify_ssl']) {
            $http = $http->withoutVerifying();
        }

        if ($config['auth_type'] === 'basic') {
            $http = $http->withBasicAuth($config['username'], $credential);
        } else {
            $http = $http->withToken($credential);
        }

        $allDevices = [];
        $offset = 0;
        $totalFetched = 0;

        // Build filter query parameters
        $queryParams = [
            'limit' => $batchSize,
            'offset' => $offset,
        ];

        // Add managed filter
        if ($filters['only_managed']) {
            $queryParams['DeviceManaged'] = 1;
        }

        do {
            $queryParams['offset'] = $offset;

            $response = $http->get(
                "{$config['api_url']}/api/{$config['api_version']}/devices/index",
                $queryParams
            );

            if (! $response->successful()) {
                error('Failed to fetch devices from NetMRI: ' . $response->status());
                break;
            }

            $data = $response->json();
            $devices = $data['devices'] ?? [];

            if (empty($devices)) {
                break;
            }

            // Apply filters
            foreach ($devices as $device) {
                // Apply site filters
                if (! empty($filters['include_sites'])) {
                    $deviceSite = $device['DeviceSiteName'] ?? null;
                    if (! in_array($deviceSite, $filters['include_sites'])) {
                        continue;
                    }
                }

                if (! empty($filters['exclude_sites'])) {
                    $deviceSite = $device['DeviceSiteName'] ?? null;
                    if (in_array($deviceSite, $filters['exclude_sites'])) {
                        continue;
                    }
                }

                // Apply zone filters
                if (! empty($filters['include_zones'])) {
                    $deviceZone = $device['DeviceZoneName'] ?? null;
                    if (! in_array($deviceZone, $filters['include_zones'])) {
                        continue;
                    }
                }

                if (! empty($filters['exclude_zones'])) {
                    $deviceZone = $device['DeviceZoneName'] ?? null;
                    if (in_array($deviceZone, $filters['exclude_zones'])) {
                        continue;
                    }
                }

                // Apply device type filters
                if (! empty($filters['include_device_types'])) {
                    $deviceType = $device['DeviceType'] ?? null;
                    if (! in_array($deviceType, $filters['include_device_types'])) {
                        continue;
                    }
                }

                if (! empty($filters['exclude_device_types'])) {
                    $deviceType = $device['DeviceType'] ?? null;
                    if (in_array($deviceType, $filters['exclude_device_types'])) {
                        continue;
                    }
                }

                $allDevices[] = $device;
                $totalFetched++;

                // Check max devices limit
                if ($maxDevices && $totalFetched >= $maxDevices) {
                    break 2;
                }
            }

            $offset += $batchSize;

            // Check if we've fetched all devices
            if (count($devices) < $batchSize) {
                break;
            }
        } while (true);

        return [
            'devices' => $allDevices,
            'total' => $totalFetched,
        ];
    }

    protected function processDevice($device, $mapping)
    {
        $deviceName = $device['DeviceName'] ?? $device['device_name'] ?? 'unknown';
        $deviceIp = $device['DeviceIPAddress'] ?? $device['DeviceIP'] ?? $device['ip_address'] ?? null;
        $deviceModel = $device['DeviceModel'] ?? $device['model'] ?? $mapping['device_type'];
        $deviceSite = $device['DeviceSiteName'] ?? null;
        $deviceZone = $device['DeviceZoneName'] ?? null;

        if (! $deviceIp) {
            throw new \Exception('Device IP address not found');
        }

        // Build output
        $output = [
            'device_name' => $deviceName,
            'device_ip' => $deviceIp,
            'device_model' => $deviceModel,
            'template_id' => $mapping['template_id'],
            'vendor_id' => $mapping['vendor_id'],
            'device_category_id' => $mapping['category_id'],
            'device_cred_id' => $mapping['credential_id'],
            'prompts' => [
                'device_enable_prompt' => str_replace('{device_name}', $deviceName, $mapping['prompts']['device_enable_prompt']),
                'device_main_prompt' => str_replace('{device_name}', $deviceName, $mapping['prompts']['device_main_prompt']),
            ],
            'tags' => $mapping['tags'],
            'netmri_device_type' => $device['DeviceType'] ?? 'unknown',
            'netmri_site' => $deviceSite,
            'netmri_zone' => $deviceZone,
            'connection_type' => 'ssh',
            'port' => 22,
        ];

        // Add default group if specified in mapping
        if (isset($mapping['default_group_id']) && $mapping['default_group_id']) {
            $output['group_id'] = $mapping['default_group_id'];
        }

        // Add additional tags based on site/zone mapping
        if (isset($mapping['site_zone_tag_mapping']) && ! empty($mapping['site_zone_tag_mapping'])) {
            $additionalTags = [];

            if ($deviceSite && isset($mapping['site_zone_tag_mapping'][$deviceSite])) {
                $additionalTags[] = $mapping['site_zone_tag_mapping'][$deviceSite];
            }

            if ($deviceZone && isset($mapping['site_zone_tag_mapping'][$deviceZone])) {
                $additionalTags[] = $mapping['site_zone_tag_mapping'][$deviceZone];
            }

            if (! empty($additionalTags)) {
                $output['tags'] = array_unique(array_merge($output['tags'], $additionalTags));
            }
        }

        return $output;
    }

    protected function displaySummary($devices)
    {
        note(count($devices) . ' devices processed successfully');

        // Group by device type
        $byType = [];
        foreach ($devices as $device) {
            $type = $device['device_model'];
            if (! isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;
        }

        info('Device type breakdown:');
        foreach ($byType as $type => $count) {
            note("- $type: $count devices");
        }

        // Group by site
        $bySite = [];
        foreach ($devices as $device) {
            $site = $device['netmri_site'] ?? 'Unknown';
            if (! isset($bySite[$site])) {
                $bySite[$site] = 0;
            }
            $bySite[$site]++;
        }

        if (count($bySite) > 1) {
            info('Site breakdown:');
            foreach ($bySite as $site => $count) {
                note("- $site: $count devices");
            }
        }

        // Group by zone
        $byZone = [];
        foreach ($devices as $device) {
            $zone = $device['netmri_zone'] ?? 'Unknown';
            if (! isset($byZone[$zone])) {
                $byZone[$zone] = 0;
            }
            $byZone[$zone]++;
        }

        if (count($byZone) > 1) {
            info('Zone breakdown:');
            foreach ($byZone as $zone => $count) {
                note("- $zone: $count devices");
            }
        }
    }

    protected function showInfo()
    {
        info('===== rConfig NetMRI Device Load Tool =====');

        note('PURPOSE:');
        $this->line('This command loads devices from NetMRI via API and creates a JSON file');
        $this->line('for bulk import into rConfig. It applies filters, maps device types,');
        $this->line('and assigns credentials based on configured mappings.');

        note('PREREQUISITES:');
        $this->line('1. NetMRI connection configured (rconfig:netmri-connection --setup)');
        $this->line('2. Device type mappings created (rconfig:netmri-device-mappings --add)');
        $this->line('3. Credential sets created in rConfig');

        note('FEATURES:');
        $this->line('- Connects to NetMRI API with authentication');
        $this->line('- Applies site, zone, and device type filters');
        $this->line('- Paginates through large device inventories');
        $this->line('- Maps NetMRI device types to rConfig configuration');
        $this->line('- Assigns credentials from mappings');
        $this->line('- Tags devices based on site/zone');
        $this->line('- Generates timestamped import JSON');

        note('USAGE:');
        $this->line('php artisan rconfig:netmri-load-devices');
        $this->line('php artisan rconfig:netmri-load-devices --batch-size=200');
        $this->line('php artisan rconfig:netmri-load-devices --max-devices=50');
        $this->line('php artisan rconfig:netmri-load-devices --info');

        note('OPTIONS:');
        $this->line('--batch-size=N    Number of devices to fetch per API call (default: 100)');
        $this->line('--max-devices=N   Limit total devices imported (useful for testing)');

        note('WORKFLOW:');
        $this->line('1. Setup connection:    php artisan rconfig:netmri-connection --setup');
        $this->line('2. Configure filters:   php artisan rconfig:netmri-connection --edit-filters');
        $this->line('3. Create mappings:     php artisan rconfig:netmri-device-mappings --add');
        $this->line('4. Load devices:        php artisan rconfig:netmri-load-devices');
        $this->line('5. Import to rConfig:   php artisan rconfig:netmri-import-devices');

        note('RELATED COMMANDS:');
        $this->line('rconfig:netmri-connection       - Manage NetMRI API connection');
        $this->line('rconfig:netmri-device-mappings  - Manage device type mappings');
        $this->line('rconfig:netmri-load-devices     - Load devices from NetMRI');
        $this->line('rconfig:netmri-import-devices   - Import devices to rConfig');
    }
}
