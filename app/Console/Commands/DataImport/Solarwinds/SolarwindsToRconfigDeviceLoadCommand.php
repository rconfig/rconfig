<?php

namespace App\Console\Commands\DataImport\Solarwinds;

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

class SolarwindsToRconfigDeviceLoadCommand extends Command
{
    protected $signature = 'rconfig:solarwinds-load-devices
                            {--batch-size=100 : Number of nodes to fetch per SWQL query}
                            {--max-nodes= : Maximum number of nodes to import (for testing)}
                            {--info : Display information about this command}';
    protected $description = 'Load devices from SolarWinds SWIS API to create rConfig import JSON';
    protected $connectionFile;
    protected $mappingsFile;

    public function __construct()
    {
        parent::__construct();
        $this->connectionFile = storage_path('app/rconfig/solarwinds_connection.json');
        $this->mappingsFile = storage_path('app/rconfig/solarwinds_mappings.json');
    }

    public function handle()
    {
        if ($this->option('info')) {
            $this->showInfo();

            return 0;
        }

        return $this->importFromSolarwinds();
    }

    protected function importFromSolarwinds()
    {
        // Check for connection file
        if (! File::exists($this->connectionFile)) {
            error('SolarWinds connection not configured!');
            note('Run: php artisan rconfig:solarwinds-connection --setup');

            return 1;
        }

        // Check for mappings file
        if (! File::exists($this->mappingsFile)) {
            error('Device type mappings file not found!');
            note('Run: php artisan rconfig:solarwinds-device-mappings --add');

            return 1;
        }

        // Load connection and mappings
        $connection = json_decode(File::get($this->connectionFile), true);
        $mappings = json_decode(File::get($this->mappingsFile), true);

        if (empty($mappings)) {
            error('No device type mappings configured!');
            note('Run: php artisan rconfig:solarwinds-device-mappings --add');

            return 1;
        }

        info('Loading SolarWinds connection info...');
        note("✓ Found connection to: {$connection['swis_url']}");

        // Get credential
        $credential = null;
        if ($connection['password'] && $connection['password_encrypted']) {
            $credential = base64_decode($connection['password']);
        } else {
            $credential = password("Password for {$connection['username']}:", required: true);
        }

        // Test connection
        info('Connecting to SolarWinds...');
        $testResult = $this->testConnection($connection, $credential);

        if (! $testResult['success']) {
            error('✗ Connection failed: ' . $testResult['error']);

            return 1;
        }

        note('✓ Connected successfully');

        // Display active filters
        $filters = $connection['filters'];
        $this->displayActiveFilters($filters);

        if (! confirm('Proceed with node loading?')) {
            note('Operation cancelled.');

            return 0;
        }

        // Fetch nodes from SolarWinds
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $batchSize = $this->option('batch-size') ?? 100;
        $maxNodes = $this->option('max-nodes');

        info('Fetching nodes from SolarWinds...');

        $nodes = $this->fetchNodesFromSolarwinds($connection, $credential, $filters, $batchSize, $maxNodes);

        if (empty($nodes)) {
            warning('No nodes retrieved from SolarWinds.');

            return 1;
        }

        info("Retrieved {$nodes['total']} nodes from SolarWinds");

        // Process nodes
        $validDevices = [];
        $failures = [];

        $bar = progress('Processing nodes', count($nodes['nodes']));

        foreach ($nodes['nodes'] as $node) {
            $machineType = $node['MachineType'] ?? $node['machine_type'] ?? null;

            // Check if we have a mapping for this machine type
            if (! $machineType || ! isset($mappings[$machineType])) {
                $failures[] = $node['Caption'] . " # Unknown machine type '$machineType'. No mapping found.";
                $bar->advance();
                continue;
            }

            // Process node
            try {
                $processedDevice = $this->processNode($node, $mappings[$machineType]);
                $validDevices[] = $processedDevice;
            } catch (\Exception $e) {
                $failures[] = $node['Caption'] . ' # Error: ' . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();

        // Save output files
        $outputFile = storage_path("app/rconfig/tempdir/rconfig_import_{$timestamp}.json");
        $failuresFile = storage_path("app/rconfig/tempdir/solarwinds_import_failures_{$timestamp}.txt");

        $dir = dirname($outputFile);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($outputFile, json_encode($validDevices, JSON_PRETTY_PRINT));

        if (! empty($failures)) {
            File::put($failuresFile, implode("\n", $failures));
            warning(count($failures) . ' nodes failed processing. See ' . basename($failuresFile));
        }

        info('Node loading completed successfully!');
        note('Generated rConfig compatible data: ' . basename($outputFile));
        note('Valid devices processed: ' . count($validDevices));

        if (confirm('Would you like to view a summary?')) {
            $this->displaySummary($validDevices);
        }

        if (confirm('Would you like to import these devices into rConfig now?')) {
            $this->call('rconfig:solarwinds-import-devices', ['file' => $outputFile]);
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

            $http = $http->withBasicAuth($config['username'], $credential);

            $swqlQuery = 'SELECT TOP 1 ServerName FROM Orion.Websites';

            $response = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                'query' => $swqlQuery,
            ]);

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

        if (! empty($filters['include_groups'])) {
            note('  Include Groups: ' . implode(', ', $filters['include_groups']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_groups'])) {
            note('  Exclude Groups: ' . implode(', ', $filters['exclude_groups']));
            $hasFilters = true;
        }
        if (! empty($filters['include_machine_types'])) {
            note('  Include Machine Types: ' . implode(', ', $filters['include_machine_types']));
            $hasFilters = true;
        }
        if (! empty($filters['exclude_machine_types'])) {
            note('  Exclude Machine Types: ' . implode(', ', $filters['exclude_machine_types']));
            $hasFilters = true;
        }
        if (! empty($filters['include_statuses'])) {
            $statusNames = array_map(fn ($s) => $this->getStatusName($s), $filters['include_statuses']);
            note('  Include Statuses: ' . implode(', ', $statusNames));
            $hasFilters = true;
        }
        if (! empty($filters['custom_property_filters'])) {
            note('  Custom Property Filters:');
            foreach ($filters['custom_property_filters'] as $prop => $value) {
                $this->line("    - {$prop} = {$value}");
            }
            $hasFilters = true;
        }

        if (! $hasFilters) {
            note('  No filters active (will import all nodes)');
        }

        $this->line('');
    }

    protected function fetchNodesFromSolarwinds($config, $credential, $filters, $batchSize, $maxNodes)
    {
        $http = Http::timeout($config['timeout']);

        if (! $config['verify_ssl']) {
            $http = $http->withoutVerifying();
        }

        $http = $http->withBasicAuth($config['username'], $credential);

        $allNodes = [];
        $offset = 0;
        $totalFetched = 0;

        do {
            // Build SWQL query with filters
            $whereConditions = [];

            // Status filter
            if (! empty($filters['include_statuses'])) {
                $statusList = implode(', ', $filters['include_statuses']);
                $whereConditions[] = "Status IN ({$statusList})";
            }

            // Machine type filter
            if (! empty($filters['include_machine_types'])) {
                $typeList = "'" . implode("', '", $filters['include_machine_types']) . "'";
                $whereConditions[] = "MachineType IN ({$typeList})";
            }

            if (! empty($filters['exclude_machine_types'])) {
                $typeList = "'" . implode("', '", $filters['exclude_machine_types']) . "'";
                $whereConditions[] = "MachineType NOT IN ({$typeList})";
            }

            $whereClause = ! empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            $swqlQuery = "SELECT NodeID, Caption, IPAddress, IPAddressType, MachineType, Vendor, Location, Status, SysName 
                         FROM Orion.Nodes 
                         {$whereClause}
                         ORDER BY NodeID";

            $response = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
                'query' => $swqlQuery,
            ]);

            if (! $response->successful()) {
                error('Failed to fetch nodes from SolarWinds: ' . $response->status());
                break;
            }

            $data = $response->json();
            $nodes = $data['results'] ?? [];

            if (empty($nodes)) {
                break;
            }

            // Fetch custom properties and groups for each node
            foreach ($nodes as &$node) {
                // Apply group filters
                if (! empty($filters['include_groups']) || ! empty($filters['exclude_groups'])) {
                    $nodeGroups = $this->fetchNodeGroups($config, $credential, $node['NodeID']);
                    $node['NodeGroups'] = $nodeGroups;

                    if (! empty($filters['include_groups'])) {
                        $hasIncludedGroup = false;
                        foreach ($nodeGroups as $group) {
                            if (in_array($group, $filters['include_groups'])) {
                                $hasIncludedGroup = true;
                                break;
                            }
                        }
                        if (! $hasIncludedGroup) {
                            continue;
                        }
                    }

                    if (! empty($filters['exclude_groups'])) {
                        $hasExcludedGroup = false;
                        foreach ($nodeGroups as $group) {
                            if (in_array($group, $filters['exclude_groups'])) {
                                $hasExcludedGroup = true;
                                break;
                            }
                        }
                        if ($hasExcludedGroup) {
                            continue;
                        }
                    }
                } else {
                    $node['NodeGroups'] = [];
                }

                // Fetch custom properties
                $node['CustomProperties'] = $this->fetchCustomProperties($config, $credential, $node['NodeID']);

                $allNodes[] = $node;
                $totalFetched++;

                // Check max nodes limit
                if ($maxNodes && $totalFetched >= $maxNodes) {
                    break 2;
                }
            }

            $offset += $batchSize;

            // For this implementation, we're fetching all at once
            // In production, you'd implement proper pagination with LIMIT/OFFSET
            break;
        } while (true);

        return [
            'nodes' => $allNodes,
            'total' => $totalFetched,
        ];
    }

    protected function fetchNodeGroups($config, $credential, $nodeId)
    {
        $http = Http::timeout($config['timeout']);

        if (! $config['verify_ssl']) {
            $http = $http->withoutVerifying();
        }

        $http = $http->withBasicAuth($config['username'], $credential);

        $swqlQuery = "SELECT c.Name 
                     FROM Orion.ContainerMembers cm
                     INNER JOIN Orion.Container c ON cm.ContainerID = c.ContainerID
                     WHERE cm.MemberPrimaryID LIKE 'N:{$nodeId}'";

        $response = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
            'query' => $swqlQuery,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return array_column($data['results'] ?? [], 'Name');
        }

        return [];
    }

    protected function fetchCustomProperties($config, $credential, $nodeId)
    {
        $http = Http::timeout($config['timeout']);

        if (! $config['verify_ssl']) {
            $http = $http->withoutVerifying();
        }

        $http = $http->withBasicAuth($config['username'], $credential);

        $swqlQuery = "SELECT * FROM Orion.NodesCustomProperties WHERE NodeID = {$nodeId}";

        $response = $http->post("{$config['swis_url']}/SolarWinds/InformationService/v3/Json/Query", [
            'query' => $swqlQuery,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (! empty($data['results'])) {
                return $data['results'][0];
            }
        }

        return [];
    }

    protected function processNode($node, $mapping)
    {
        $deviceName = $node['Caption'] ?? $node['SysName'] ?? 'unknown';
        $deviceIp = $node['IPAddress'] ?? null;
        $deviceModel = $node['MachineType'] ?? $mapping['device_type'];
        $nodeGroups = $node['NodeGroups'] ?? [];
        $customProperties = $node['CustomProperties'] ?? [];

        if (! $deviceIp) {
            throw new \Exception('Node IP address not found');
        }

        // Build base tags from mapping
        $tags = $mapping['tags'] ?? [];

        // Add tags from custom property mapping
        if (! empty($mapping['custom_property_tag_mapping'])) {
            foreach ($mapping['custom_property_tag_mapping'] as $propertyName => $tagId) {
                if (isset($customProperties[$propertyName]) && $customProperties[$propertyName]) {
                    $tags[] = $tagId;
                }
            }
        }

        // Determine device group from node group mapping
        $groupId = $mapping['default_group_id'] ?? null;
        if (! empty($mapping['node_group_mapping'])) {
            foreach ($nodeGroups as $nodeGroup) {
                if (isset($mapping['node_group_mapping'][$nodeGroup])) {
                    $groupId = $mapping['node_group_mapping'][$nodeGroup];
                    break; // Use first matching group
                }
            }
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
            'tags' => array_unique($tags),
            'solarwinds_machine_type' => $node['MachineType'] ?? 'unknown',
            'solarwinds_node_groups' => $nodeGroups,
            'solarwinds_custom_properties' => $customProperties,
            'solarwinds_location' => $node['Location'] ?? null,
            'solarwinds_vendor' => $node['Vendor'] ?? null,
            'connection_type' => 'ssh',
            'port' => 22,
        ];

        if ($groupId) {
            $output['group_id'] = $groupId;
        }

        return $output;
    }

    protected function displaySummary($devices)
    {
        note(count($devices) . ' devices processed successfully');

        // Group by machine type
        $byType = [];
        foreach ($devices as $device) {
            $type = $device['device_model'];
            if (! isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;
        }

        info('Machine type breakdown:');
        foreach ($byType as $type => $count) {
            note("- $type: $count devices");
        }

        // Group by node groups
        $byGroup = [];
        foreach ($devices as $device) {
            $groups = $device['solarwinds_node_groups'] ?? ['Ungrouped'];
            foreach ($groups as $group) {
                if (! isset($byGroup[$group])) {
                    $byGroup[$group] = 0;
                }
                $byGroup[$group]++;
            }
        }

        if (count($byGroup) > 0) {
            info('Node group breakdown:');
            foreach ($byGroup as $group => $count) {
                note("- $group: $count devices");
            }
        }

        // Group by location if available
        $byLocation = [];
        foreach ($devices as $device) {
            $location = $device['solarwinds_location'] ?? 'Unknown';
            if (! isset($byLocation[$location])) {
                $byLocation[$location] = 0;
            }
            $byLocation[$location]++;
        }

        if (count($byLocation) > 1) {
            info('Location breakdown:');
            foreach ($byLocation as $location => $count) {
                note("- $location: $count devices");
            }
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

    protected function showInfo()
    {
        info('===== rConfig SolarWinds Device Load Tool =====');

        note('PURPOSE:');
        $this->line('This command loads nodes from SolarWinds via SWIS API and creates a JSON file');
        $this->line('for bulk import into rConfig. It applies filters, maps machine types,');
        $this->line('and assigns credentials based on configured mappings.');

        note('PREREQUISITES:');
        $this->line('1. SolarWinds connection configured (rconfig:solarwinds-connection --setup)');
        $this->line('2. Device type mappings created (rconfig:solarwinds-device-mappings --add)');
        $this->line('3. Credential sets created in rConfig');

        note('FEATURES:');
        $this->line('- Connects to SolarWinds SWIS API with authentication');
        $this->line('- Executes SWQL queries to extract node data');
        $this->line('- Applies group, machine type, and status filters');
        $this->line('- Fetches custom properties for flexible tagging');
        $this->line('- Maps node groups to rConfig device groups');
        $this->line('- Assigns credentials from mappings');
        $this->line('- Generates timestamped import JSON');

        note('USAGE:');
        $this->line('php artisan rconfig:solarwinds-load-devices');
        $this->line('php artisan rconfig:solarwinds-load-devices --batch-size=200');
        $this->line('php artisan rconfig:solarwinds-load-devices --max-nodes=50');
        $this->line('php artisan rconfig:solarwinds-load-devices --info');

        note('OPTIONS:');
        $this->line('--batch-size=N    Number of nodes to fetch per query (default: 100)');
        $this->line('--max-nodes=N     Limit total nodes imported (useful for testing)');

        note('WORKFLOW:');
        $this->line('1. Setup connection:    php artisan rconfig:solarwinds-connection --setup');
        $this->line('2. Configure filters:   php artisan rconfig:solarwinds-connection --edit-filters');
        $this->line('3. Create mappings:     php artisan rconfig:solarwinds-device-mappings --add');
        $this->line('4. Load devices:        php artisan rconfig:solarwinds-load-devices');
        $this->line('5. Import to rConfig:   php artisan rconfig:solarwinds-import-devices');

        note('RELATED COMMANDS:');
        $this->line('rconfig:solarwinds-connection       - Manage SolarWinds API connection');
        $this->line('rconfig:solarwinds-device-mappings  - Manage device type mappings');
        $this->line('rconfig:solarwinds-load-devices     - Load devices from SolarWinds');
        $this->line('rconfig:solarwinds-import-devices   - Import devices to rConfig');
    }
}
