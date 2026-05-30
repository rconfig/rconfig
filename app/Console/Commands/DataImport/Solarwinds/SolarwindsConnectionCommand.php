<?php

namespace App\Console\Commands\DataImport\Solarwinds;

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

class SolarwindsConnectionCommand extends Command
{
    protected $signature = 'rconfig:solarwinds-connection
                            {--setup : Setup SolarWinds SWIS connection interactively}
                            {--test : Test existing connection}
                            {--show : Show current connection info}
                            {--edit-filters : Edit node filters}
                            {--set-url= : Update SWIS URL}
                            {--clear : Clear connection info}
                            {--info : Display information about this command}';
    protected $description = 'Manage SolarWinds SWIS API connection configuration';
    protected $connectionFile;
    protected $stubFile;

    public function __construct()
    {
        parent::__construct();
        $this->connectionFile = storage_path('app/rconfig/solarwinds_connection.json');
        $this->stubFile = storage_path('app/rconfig/solarwinds_connection.stub.json');
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
        note('SolarWinds SWIS Connection Management');

        $action = select(
            'What would you like to do?',
            [
                'info' => 'Show information about this command',
                'setup' => 'Setup SolarWinds connection',
                'test' => 'Test existing connection',
                'show' => 'Show current connection info',
                'edit-filters' => 'Edit node filters',
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
        info('SolarWinds SWIS Connection Setup');
        $this->line('');

        // Get SWIS details
        $swisUrl = text(
            'SolarWinds SWIS URL (e.g., https://solarwinds.company.com:17778):',
            required: true,
            validate: fn ($value) => filter_var($value, FILTER_VALIDATE_URL) ? null : 'Invalid URL format'
        );

        $swisUrl = rtrim($swisUrl, '/');

        $username = text('Username:', required: true);
        $password = password('Password:', required: true);

        $storePassword = confirm('Store password in config file? (Not recommended for production)', false);

        $verifySsl = confirm('Verify SSL certificates?', true);

        // Create connection config
        $config = [
            'swis_url' => $swisUrl,
            'username' => $username,
            'password' => $storePassword ? base64_encode($password) : null,
            'password_encrypted' => $storePassword,
            'verify_ssl' => $verifySsl,
            'timeout' => 30,
            'created_at' => Carbon::now()->toIso8601String(),
            'last_tested' => null,
            'connection_status' => 'untested',
            'solarwinds_version' => null,
            'node_count' => null,
            'filters' => [
                'include_groups' => [],
                'exclude_groups' => [],
                'include_machine_types' => [],
                'exclude_machine_types' => [],
                'include_statuses' => [1], // 1 = Up
                'custom_property_filters' => [],
            ],
        ];

        // Test connection
        $this->line('');
        info('Testing connection to SolarWinds...');

        $testResult = $this->performConnectionTest($config, $password);

        if ($testResult['success']) {
            $config['last_tested'] = Carbon::now()->toIso8601String();
            $config['connection_status'] = 'success';
            $config['solarwinds_version'] = $testResult['version'] ?? 'unknown';
            $config['node_count'] = $testResult['node_count'] ?? 0;

            File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

            info('✓ Connection successful!');
            note("✓ SolarWinds version: {$config['solarwinds_version']}");
            note("✓ Found {$config['node_count']} nodes");
            $this->line('');
            info('Connection info saved to: ' . basename($this->connectionFile));
            $this->line('');

            note('Next steps:');
            $this->line('1. Configure filters (optional): php artisan rconfig:solarwinds-connection --edit-filters');
            $this->line('2. Create device mappings: php artisan rconfig:solarwinds-device-mappings');
            $this->line('3. Load devices: php artisan rconfig:solarwinds-load-devices');

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
            error('No connection configuration found.');
            note('Run: php artisan rconfig:solarwinds-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info("Testing connection to: {$config['swis_url']}");

        // Get password if needed
        $credential = null;
        if ($config['password'] && $config['password_encrypted']) {
            $credential = base64_decode($config['password']);
        } else {
            $credential = password("Password for {$config['username']}:", required: true);
        }

        $result = $this->performConnectionTest($config, $credential);

        if ($result['success']) {
            // Update connection status
            $config['last_tested'] = Carbon::now()->toIso8601String();
            $config['connection_status'] = 'success';
            $config['solarwinds_version'] = $result['version'] ?? 'unknown';
            $config['node_count'] = $result['node_count'] ?? 0;

            File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

            info('✓ Connection successful!');
            note("SolarWinds version: {$config['solarwinds_version']}");
            note("Total nodes: {$config['node_count']}");

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

            $http = $http->withBasicAuth($config['username'], $credential);

            // Test with a simple SWQL query
            $swqlQuery = 'SELECT TOP 1 ServerName, ServerVersion FROM Orion.Websites';

            $response = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                'query' => $swqlQuery,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $version = $data['results'][0]['ServerVersion'] ?? 'unknown';

                // Get node count
                $nodeCountQuery = 'SELECT COUNT(NodeID) as NodeCount FROM Orion.Nodes';
                $nodeResponse = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                    'query' => $nodeCountQuery,
                ]);

                $nodeCount = 0;
                if ($nodeResponse->successful()) {
                    $nodeData = $nodeResponse->json();
                    $nodeCount = $nodeData['results'][0]['NodeCount'] ?? 0;
                }

                return [
                    'success' => true,
                    'version' => $version,
                    'node_count' => $nodeCount,
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
            error('No connection configuration found.');
            note('Run: php artisan rconfig:solarwinds-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info('Current SolarWinds Connection Configuration:');
        $this->line('');

        note("SWIS URL: {$config['swis_url']}");
        note("Username: {$config['username']}");
        note('Password: ' . ($config['password'] ? '********' : 'not stored (will prompt)'));
        note('Verify SSL: ' . ($config['verify_ssl'] ? 'yes' : 'no'));
        note("Timeout: {$config['timeout']}s");
        $this->line('');

        note("Connection Status: {$config['connection_status']}");
        if ($config['last_tested']) {
            note("Last Tested: {$config['last_tested']}");
        }
        if ($config['solarwinds_version']) {
            note("SolarWinds Version: {$config['solarwinds_version']}");
        }
        if ($config['node_count']) {
            note("Total Nodes: {$config['node_count']}");
        }

        $this->line('');
        info('Active Filters:');

        $filters = $config['filters'];
        $hasFilters = false;

        if (! empty($filters['include_groups'])) {
            note('Include Groups: ' . implode(', ', $filters['include_groups']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_groups'])) {
            note('Exclude Groups: ' . implode(', ', $filters['exclude_groups']));
            $hasFilters = true;
        }
        if (! empty($filters['include_machine_types'])) {
            note('Include Machine Types: ' . implode(', ', $filters['include_machine_types']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_machine_types'])) {
            note('Exclude Machine Types: ' . implode(', ', $filters['exclude_machine_types']));
            $hasFilters = true;
        }
        if (! empty($filters['include_statuses'])) {
            $statusNames = array_map(fn ($s) => $this->getStatusName($s), $filters['include_statuses']);
            note('Include Statuses: ' . implode(', ', $statusNames));
            $hasFilters = true;
        }
        if (! empty($filters['custom_property_filters'])) {
            note('Custom Property Filters:');
            foreach ($filters['custom_property_filters'] as $prop => $value) {
                $this->line("  - {$prop} = {$value}");
            }
            $hasFilters = true;
        }

        if (! $hasFilters) {
            note('No filters configured (will import all nodes)');
        }

        return 0;
    }

    protected function editFilters()
    {
        if (! File::exists($this->connectionFile)) {
            error('No connection configuration found.');
            note('Run: php artisan rconfig:solarwinds-connection --setup');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);

        info('SolarWinds Node Filters');
        $this->line('');

        // Get credentials for SWQL queries
        $credential = $this->getCredential($config);
        if (! $credential) {
            return 1;
        }

        // Fetch available groups, machine types from SolarWinds
        $metadata = $this->fetchSolarwindsMetadata($config, $credential);

        if (! $metadata['success']) {
            error('Failed to fetch SolarWinds metadata: ' . $metadata['error']);

            return 1;
        }

        // Configure node group filters
        if (! empty($metadata['groups'])) {
            $this->line('');
            info('Available Node Groups in SolarWinds:');
            foreach ($metadata['groups'] as $group) {
                $this->line("  - {$group['name']} ({$group['member_count']} nodes)");
            }

            $groupFilter = select(
                'Filter by node groups?',
                [
                    'all' => 'Import all groups',
                    'include' => 'Include specific groups',
                    'exclude' => 'Exclude specific groups',
                ],
                'all'
            );

            if ($groupFilter === 'include') {
                $groupOptions = array_combine(
                    array_column($metadata['groups'], 'name'),
                    array_map(fn ($g) => "{$g['name']} ({$g['member_count']} nodes)", $metadata['groups'])
                );
                $config['filters']['include_groups'] = multiselect(
                    'Select groups to include:',
                    $groupOptions
                );
                $config['filters']['exclude_groups'] = [];
            } elseif ($groupFilter === 'exclude') {
                $groupOptions = array_combine(
                    array_column($metadata['groups'], 'name'),
                    array_map(fn ($g) => "{$g['name']} ({$g['member_count']} nodes)", $metadata['groups'])
                );
                $config['filters']['exclude_groups'] = multiselect(
                    'Select groups to exclude:',
                    $groupOptions
                );
                $config['filters']['include_groups'] = [];
            } else {
                $config['filters']['include_groups'] = [];
                $config['filters']['exclude_groups'] = [];
            }
        }

        // Configure machine type filters
        if (! empty($metadata['machine_types'])) {
            $this->line('');
            info('Available Machine Types:');
            foreach ($metadata['machine_types'] as $type) {
                $this->line("  - {$type['name']} ({$type['node_count']} nodes)");
            }

            $typeFilter = select(
                'Filter by machine types?',
                [
                    'all' => 'Import all machine types',
                    'include' => 'Include specific types',
                    'exclude' => 'Exclude specific types',
                ],
                'all'
            );

            if ($typeFilter === 'include') {
                $typeOptions = array_combine(
                    array_column($metadata['machine_types'], 'name'),
                    array_map(fn ($t) => "{$t['name']} ({$t['node_count']} nodes)", $metadata['machine_types'])
                );
                $config['filters']['include_machine_types'] = multiselect(
                    'Select machine types to include:',
                    $typeOptions
                );
                $config['filters']['exclude_machine_types'] = [];
            } elseif ($typeFilter === 'exclude') {
                $typeOptions = array_combine(
                    array_column($metadata['machine_types'], 'name'),
                    array_map(fn ($t) => "{$t['name']} ({$t['node_count']} nodes)", $metadata['machine_types'])
                );
                $config['filters']['exclude_machine_types'] = multiselect(
                    'Select machine types to exclude:',
                    $typeOptions
                );
                $config['filters']['include_machine_types'] = [];
            } else {
                $config['filters']['include_machine_types'] = [];
                $config['filters']['exclude_machine_types'] = [];
            }
        }

        // Configure status filters
        $this->line('');
        $statusOptions = [
            1 => 'Up',
            2 => 'Down',
            3 => 'Warning',
            9 => 'Unknown',
        ];

        $selectedStatuses = multiselect(
            'Which node statuses to include?',
            $statusOptions,
            [1] // Default to "Up" only
        );

        $config['filters']['include_statuses'] = $selectedStatuses;

        // Save updated config
        File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

        $this->line('');
        info('Filters saved!');

        return 0;
    }

    protected function fetchSolarwindsMetadata($config, $credential)
    {
        try {
            $http = Http::timeout($config['timeout']);

            if (! $config['verify_ssl']) {
                $http = $http->withoutVerifying();
            }

            $http = $http->withBasicAuth($config['username'], $credential);

            $metadata = [
                'success' => true,
                'groups' => [],
                'machine_types' => [],
            ];

            // Fetch node groups
            $groupsQuery = "SELECT Name, COUNT(m.NodeID) as MemberCount 
                           FROM Orion.ContainerMembers m
                           INNER JOIN Orion.Container c ON m.ContainerID = c.ContainerID
                           WHERE c.Owner = 'Core'
                           GROUP BY Name";

            $groupsResponse = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                'query' => $groupsQuery,
            ]);

            if ($groupsResponse->successful()) {
                $groupsData = $groupsResponse->json();
                foreach ($groupsData['results'] ?? [] as $group) {
                    $metadata['groups'][] = [
                        'name' => $group['Name'] ?? 'Unknown',
                        'member_count' => $group['MemberCount'] ?? 0,
                    ];
                }
            }

            // Fetch machine types
            $typesQuery = 'SELECT MachineType, COUNT(NodeID) as NodeCount 
                          FROM Orion.Nodes 
                          WHERE MachineType IS NOT NULL 
                          GROUP BY MachineType';

            $typesResponse = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                'query' => $typesQuery,
            ]);

            if ($typesResponse->successful()) {
                $typesData = $typesResponse->json();
                foreach ($typesData['results'] ?? [] as $type) {
                    $metadata['machine_types'][] = [
                        'name' => $type['MachineType'] ?? 'Unknown',
                        'node_count' => $type['NodeCount'] ?? 0,
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

    protected function setUrl($url)
    {
        if (! File::exists($this->connectionFile)) {
            error('No connection configuration found.');

            return 1;
        }

        $config = json_decode(File::get($this->connectionFile), true);
        $config['swis_url'] = rtrim($url, '/');

        File::put($this->connectionFile, json_encode($config, JSON_PRETTY_PRINT));

        info("SWIS URL updated to: {$url}");
        note('Run --test to verify the new URL');

        return 0;
    }

    protected function clearConnection()
    {
        if (! File::exists($this->connectionFile)) {
            note('No connection configuration found.');

            return 0;
        }

        if (! confirm('Are you sure you want to clear the SolarWinds connection configuration?', false)) {
            note('Operation cancelled.');

            return 0;
        }

        File::delete($this->connectionFile);
        info('Connection configuration cleared.');

        return 0;
    }

    protected function getCredential($config)
    {
        if ($config['password'] && $config['password_encrypted']) {
            return base64_decode($config['password']);
        } else {
            return password("Password for {$config['username']}:", required: true);
        }
    }

    protected function getStatusName($status)
    {
        $statuses = [
            1 => 'Up',
            2 => 'Down',
            3 => 'Warning',
            9 => 'Unknown',
        ];

        return $statuses[$status] ?? "Status {$status}";
    }

    protected function createStubFile()
    {
        $stub = [
            '_comment' => 'SolarWinds SWIS API Connection Configuration Template',
            '_instructions' => [
                '1. Copy this file to solarwinds_connection.json',
                '2. Fill in your SolarWinds details',
                '3. Run: php artisan rconfig:solarwinds-connection --test',
                '4. Delete this stub file when done',
            ],
            'swis_url' => 'https://your-solarwinds-server.company.com:17778',
            'username' => 'your_api_username',
            'password' => null,
            'password_encrypted' => false,
            'verify_ssl' => true,
            'timeout' => 30,
            '_filter_instructions' => [
                '_note' => 'Leave arrays empty [] to import all',
                '_include_vs_exclude' => 'Use include OR exclude, not both',
                '_status_values' => '1=Up, 2=Down, 3=Warning, 9=Unknown',
                '_examples' => [
                    'include_groups' => ['Core Routers', 'Distribution Switches'],
                    'exclude_machine_types' => ['Unknown', 'Unmanaged'],
                ],
            ],
            'filters' => [
                'include_groups' => [],
                'exclude_groups' => [],
                'include_machine_types' => [],
                'exclude_machine_types' => [],
                'include_statuses' => [1],
                'custom_property_filters' => [],
            ],
        ];

        File::put($this->stubFile, json_encode($stub, JSON_PRETTY_PRINT));
    }

    protected function showInfo()
    {
        $this->line('');
        info('===== SolarWinds SWIS Connection Management =====');
        $this->line('');

        note('PURPOSE:');
        $this->line('This command manages the connection configuration for SolarWinds SWIS API access.');
        $this->line('It stores connection details, tests connectivity, and manages node filters.');
        $this->line('');

        note('CONNECTION FILE:');
        $this->line('Connection info is stored in: ' . basename($this->connectionFile));
        $this->line('Template file available at: ' . basename($this->stubFile));
        $this->line('');

        note('AUTHENTICATION:');
        $this->line('SolarWinds SWIS uses username/password authentication.');
        $this->line('Passwords can be stored (encrypted) or prompted for each use.');
        $this->line('For production, prompting is more secure.');
        $this->line('');

        note('SWIS API:');
        $this->line('SWIS (SolarWinds Information Service) is a REST API that accepts SWQL queries.');
        $this->line('SWQL (SolarWinds Query Language) is SQL-like and allows complex filtering.');
        $this->line('Default port: 17778');
        $this->line('');

        note('FILTERS:');
        $this->line('Node filters allow you to selectively import devices based on:');
        $this->line('  - Node Groups: Import/exclude specific groups (Core, Distribution, etc.)');
        $this->line('  - Machine Types: Import/exclude specific device types');
        $this->line('  - Status: Import only Up, Down, Warning, or Unknown nodes');
        $this->line('  - Custom Properties: Filter by any custom property value');
        $this->line('');

        note('USAGE:');
        $this->line('php artisan rconfig:solarwinds-connection --setup           Setup new connection');
        $this->line('php artisan rconfig:solarwinds-connection --test            Test existing connection');
        $this->line('php artisan rconfig:solarwinds-connection --show            Show current config');
        $this->line('php artisan rconfig:solarwinds-connection --edit-filters    Configure node filters');
        $this->line('php artisan rconfig:solarwinds-connection --set-url=URL     Update SWIS URL');
        $this->line('php artisan rconfig:solarwinds-connection --clear           Clear configuration');
        $this->line('');

        note('WORKFLOW:');
        $this->line('1. Setup connection:     php artisan rconfig:solarwinds-connection --setup');
        $this->line('2. Configure filters:    php artisan rconfig:solarwinds-connection --edit-filters');
        $this->line('3. Create mappings:      php artisan rconfig:solarwinds-device-mappings');
        $this->line('4. Load devices:         php artisan rconfig:solarwinds-load-devices');
        $this->line('5. Import devices:       php artisan rconfig:solarwinds-import-devices');
        $this->line('');

        note('RELATED COMMANDS:');
        $this->line('rconfig:solarwinds-connection       - Manage SolarWinds API connection');
        $this->line('rconfig:solarwinds-device-mappings  - Manage device type mappings');
        $this->line('rconfig:solarwinds-load-devices     - Load devices from SolarWinds');
        $this->line('rconfig:solarwinds-import-devices   - Import devices to rConfig');
    }
}
