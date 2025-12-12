<?php

namespace App\Console\Commands\DataImport;

use App\Models\DeviceCredentials;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class RancidToRconfigDeviceLoadCommand extends Command
{
    protected $signature = 'rconfig:rancid-load-devices
                            {--rancid-base= : Path to RANCID base directory (default: /usr/local/rancid or /var/lib/rancid)}
                            {--group= : Specific RANCID group to import (e.g. "networking")}
                            {--info : Display information about this command}';
    protected $description = 'Load devices from RANCID to create rConfig import JSON';

    /**
     * Storage for selected credential ID to use as default
     */
    protected $defaultCredentialId = null;

    /**
     * RANCID base directory path
     */
    protected $rancidBasePath = null;
    protected $varPath = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show info or menu if no options provided
        if ($this->option('info')) {
            $this->showStartMenu();

            return 0;
        }

        return $this->importFromRancid();
    }

    /**
     * Show the start menu with options
     */
    protected function showStartMenu()
    {
        note('rConfig RANCID Import Utility');

        $options = [
            'info' => 'Show information about this command',
            'import' => 'Import devices from RANCID',
            'exit' => 'Exit',
        ];

        $action = select(
            'What would you like to do?',
            $options,
            'info'
        );

        switch ($action) {
            case 'info':
                $this->showInfo();
                break;
            case 'import':
                $this->importFromRancid();
                break;
            case 'exit':
                info('Goodbye!');

                return;
        }
    }

    /**
     * Main import process from RANCID
     */
    protected function importFromRancid()
    {
        // Determine RANCID base path
        $this->rancidBasePath = $this->option('rancid-base');

        if (! $this->rancidBasePath) {
            // Try common RANCID installation paths
            $commonPaths = [
                '/usr/local/rancid',
                '/var/lib/rancid',
                '/var/rancid',
                '/opt/rancid',
            ];

            foreach ($commonPaths as $path) {
                if (File::exists($path)) {
                    $this->rancidBasePath = $path;
                    break;
                }
            }

            if (! $this->rancidBasePath) {
                error('Could not find RANCID installation directory.');
                $customPath = text('Please enter the path to your RANCID base directory:');

                if (! File::exists($customPath)) {
                    error('Directory does not exist: ' . $customPath);

                    return 1;
                }

                $this->rancidBasePath = $customPath;
            }
        }

        info('Using RANCID base directory: ' . $this->rancidBasePath);

        // Check for mappings file
        $mappingsFile = storage_path('app/rconfig/rancid_mappings.json');

        if (! File::exists($mappingsFile)) {
            error('Device type mappings file not found!');
            note('The mappings file is required to translate RANCID device types to rConfig settings.');
            note('You need to create device type mappings before importing devices.');

            if (confirm('Would you like to create device mappings now?')) {
                $this->call('rconfig:rancid-device-mappings');

                if (! File::exists($mappingsFile)) {
                    error("Mappings file still doesn't exist. Please create at least one device mapping.");

                    return 1;
                }

                note('Mappings created successfully. Continuing with import...');
            } else {
                note("Please run 'php artisan rconfig:rancid-device-mappings' to create mappings first.");

                return 1;
            }
        }

        // Find available RANCID groups
        $varPath = $this->rancidBasePath . '/var';
        if (! File::exists($varPath)) {
            $varPath = $this->rancidBasePath;
            info('Using RHEL/Rocky Linux structure (groups directly under base path)');
            
            if (! File::exists($varPath)) {
                error('RANCID directory not found: ' . $varPath);
                return 1;
            }
        }
        $this->varPath = $varPath;
        $groups = $this->findRancidGroups($varPath);

        if (empty($groups)) {
            error('No RANCID groups found in: ' . $varPath);

            return 1;
        }

        // Select or specify group
        $selectedGroup = $this->option('group');

        if (! $selectedGroup) {
            if (count($groups) === 1) {
                $selectedGroup = $groups[0];
                info('Found single RANCID group: ' . $selectedGroup);
            } else {
                info('Found ' . count($groups) . ' RANCID groups');
                $groupOptions = array_combine($groups, $groups);
                $groupOptions['all'] = 'Import all groups';

                $selectedGroup = select(
                    'Which RANCID group would you like to import?',
                    $groupOptions
                );
            }
        }

        if ($selectedGroup === 'all') {
            $groupsToImport = $groups;
        } else {
            $groupsToImport = [$selectedGroup];
        }

        // Process each group
        $allDevices = [];
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');

        foreach ($groupsToImport as $group) {
            info('Processing RANCID group: ' . $group);

            $devices = $this->processRancidGroup($group, $timestamp);
            $allDevices = array_merge($allDevices, $devices);
        }

        if (empty($allDevices)) {
            warning('No devices were successfully processed.');

            return 1;
        }

        // Save the output data
        $outputFile = storage_path("app/rconfig/tempdir/rconfig_import_{$timestamp}.json");
        $dir = dirname($outputFile);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($outputFile, json_encode($allDevices, JSON_PRETTY_PRINT));

        info('Import process completed successfully!');
        note('Generated rConfig compatible data: ' . basename($outputFile));
        note('Total devices processed: ' . count($allDevices));

        if (confirm('Would you like to view a summary of the imported devices?')) {
            $this->displaySummary($allDevices);
        }

        if (confirm('Would you like to import these devices into rConfig now?')) {
            $this->call('rconfig:rancid-import-devices', ['file' => $outputFile]);
        }

        return 0;
    }

    /**
     * Find RANCID groups in the var directory
     */
    protected function findRancidGroups($varPath)
    {
        $groups = [];
        $directories = File::directories($varPath);

        foreach ($directories as $dir) {
            $routerDbPath = $dir . '/router.db';
            if (File::exists($routerDbPath)) {
                $groups[] = basename($dir);
            }
        }

        return $groups;
    }

    /**
     * Process a single RANCID group
     */
    protected function processRancidGroup($group, $timestamp)
    {
        $groupPath = $this->varPath . '/' . $group;
        $routerDbPath = $groupPath . '/router.db';
        $configsPath = $groupPath . '/configs';

        if (! File::exists($routerDbPath)) {
            error("router.db not found for group '$group': $routerDbPath");

            return [];
        }

        info('Reading router.db for group: ' . $group);

        // Read router.db file
        $lines = file($routerDbPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $validDevices = [];
        $failures = [];

        // Create failures log file
        $failuresFile = storage_path("app/rconfig/tempdir/rancid_import_failures_{$group}_{$timestamp}.txt");

        $bar = progress('Processing devices in ' . $group, count($lines));

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                $bar->advance();

                continue;
            }

            // Parse router.db format: hostname;device_type;status (RANCID 3.0+)
            // Support both ; (v3.0+) and : (legacy) as delimiters
            $delimiter = (strpos($line, ';') !== false) ? ';' : ':';
            $parts = explode($delimiter, trim($line));

            if (count($parts) < 3) {
                $failures[] = $line . ' # Invalid router.db format';
                $bar->advance();

                continue;
            }

            $hostname = trim($parts[0]);
            $deviceType = strtolower(trim($parts[1]));
            $status = strtolower(trim($parts[2]));

            // Skip devices marked as down
            if ($status !== 'up') {
                $failures[] = $line . ' # Device status is not "up"';
                $bar->advance();

                continue;
            }

            // Check if we have a mapping for this device type
            if (! $this->mapDeviceType($deviceType, $hostname)) {
                $failures[] = $line . " # Unknown device type '$deviceType'. No mapping found.";
                $bar->advance();

                continue;
            }

            // Try to resolve hostname to IP
            $ip = $this->resolveHostname($hostname);

            if (! $ip) {
                $failures[] = $line . ' # Could not resolve hostname to IP address';
                $bar->advance();

                continue;
            }

            // Check if config file exists
            $configFile = $configsPath . '/' . $hostname;
            if (! File::exists($configFile)) {
                $failures[] = $line . ' # Config file not found in ' . $configsPath;
                $bar->advance();

                continue;
            }

            // Extract additional info from config file if available
            $configInfo = $this->parseConfigFile($configFile);

            $deviceInfo = [
                'device_name' => $hostname,
                'device_ip' => $ip,
                'device_type' => $deviceType,
                'rancid_group' => $group,
                'config_info' => $configInfo,
            ];

            $validDevices[] = $deviceInfo;
            $bar->advance();
        }

        $bar->finish();

        // Log failures
        if (! empty($failures)) {
            $dir = dirname($failuresFile);
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            File::put($failuresFile, implode("\n", $failures));
            warning(count($failures) . ' devices failed validation in group "' . $group . '". See ' . basename($failuresFile));
        }

        // Process valid devices
        if (empty($validDevices)) {
            warning('No valid devices found in group: ' . $group);

            return [];
        }

        info('Processing ' . count($validDevices) . ' valid devices from ' . $group);

        $outputData = [];
        $processingBar = progress('Creating device records', count($validDevices));

        foreach ($validDevices as $device) {
            $outputData[] = $this->processDevice($device);
            $processingBar->advance();
        }

        $processingBar->finish();

        return $outputData;
    }

    /**
     * Resolve hostname to IP address
     */
    protected function resolveHostname($hostname)
    {
        // First check if it's already an IP address
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            return $hostname;
        }

        // Try to resolve the hostname
        $ip = gethostbyname($hostname);

        // If resolution fails, gethostbyname returns the original hostname
        if ($ip === $hostname) {
            return null;
        }

        return $ip;
    }

    /**
     * Parse config file to extract additional information
     */
    protected function parseConfigFile($configFile)
    {
        $info = [
            'hostname_from_config' => null,
            'version' => null,
            'model' => null,
        ];

        if (! File::exists($configFile)) {
            return $info;
        }

        $lines = file($configFile, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            // Try to extract hostname
            if (preg_match('/^hostname\s+(\S+)/i', $line, $matches)) {
                $info['hostname_from_config'] = $matches[1];
            }

            // Try to extract version
            if (preg_match('/IOS.*Version\s+([^,]+)/i', $line, $matches)) {
                $info['version'] = trim($matches[1]);
            }

            // Try to extract model from banner or hardware line
            if (preg_match('/cisco\s+(\S+)\s+\(.*\)\s+processor/i', $line, $matches)) {
                $info['model'] = $matches[1];
            }
        }

        return $info;
    }

    /**
     * Process a single device based on its type
     */
    protected function processDevice($device)
    {
        $deviceMapping = $this->mapDeviceType($device['device_type'], $device['device_name']);

        if (! $deviceMapping) {
            throw new \Exception('Unsupported device type: ' . $device['device_type']);
        }

        // Use model from config if available, otherwise use device type
        $deviceModel = $device['config_info']['model'] ?? $deviceMapping['device_type'];

        $output = [
            'device_name' => $device['device_name'],
            'device_ip' => $device['device_ip'],
            'device_model' => $deviceModel,
            'template_id' => $deviceMapping['template_id'],
            'vendor_id' => $deviceMapping['vendor_id'],
            'device_category_id' => $deviceMapping['category_id'],
            'prompts' => $deviceMapping['prompts'],
            'tags' => $deviceMapping['tags'],
            'rancid_group' => $device['rancid_group'],
        ];

        // Handle credentials - try to parse from .cloginrc or use default
        $credentials = $this->getCredentialsForDevice($device['device_name'], $device['device_ip']);

        if ($credentials) {
            $output['device_cred_id'] = 0;
            $output['device_username'] = $credentials['username'];
            $output['device_password'] = $credentials['password'];

            if (isset($credentials['enable_password'])) {
                $output['device_enable_password'] = $credentials['enable_password'];
            }
        } else {
            // Use default credential set
            if ($this->defaultCredentialId === null) {
                $this->defaultCredentialId = $this->askForDefaultCredential();
            }

            $output['device_cred_id'] = $this->defaultCredentialId;
        }

        // Add connection information
        $output['connection_type'] = 'ssh';
        $output['port'] = 22;

        return $output;
    }

    /**
     * Get credentials for a device from .cloginrc
     */
    protected function getCredentialsForDevice($hostname, $ip)
    {
        $cloginrcPath = $this->rancidBasePath . '/.cloginrc';

        if (! File::exists($cloginrcPath)) {
            return null;
        }

        $lines = file($cloginrcPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $credentials = [
            'username' => null,
            'password' => null,
            'enable_password' => null,
        ];

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Match hostname or IP patterns
            $matchesHost = $this->cloginrcLineMatches($line, $hostname, $ip);

            if ($matchesHost) {
                // Parse add user lines
                if (preg_match('/add\s+user\s+\S+\s+(\S+)/', $line, $matches)) {
                    $credentials['username'] = $matches[1];
                }

                // Parse add password lines: add password host password [enable_password]
                if (preg_match('/add\s+password\s+\S+\s+(\S+)(?:\s+(\S+))?/', $line, $matches)) {
                    $credentials['password'] = $matches[1];
                    if (isset($matches[2])) {
                        $credentials['enable_password'] = $matches[2];
                    }
                }
            }
        }

        // Only return if we have at least username and password
        if ($credentials['username'] && $credentials['password']) {
            return $credentials;
        }

        return null;
    }

    /**
     * Check if a .cloginrc line matches the given hostname/IP
     */
    protected function cloginrcLineMatches($line, $hostname, $ip)
    {
        // Extract the pattern from the line
        if (preg_match('/add\s+(?:user|password)\s+(\S+)/', $line, $matches)) {
            $pattern = $matches[1];

            // Convert glob pattern to regex
            $regex = str_replace(['.', '*'], ['\.', '.*'], $pattern);
            $regex = '/^' . $regex . '$/';

            // Check if hostname or IP matches
            if (preg_match($regex, $hostname) || preg_match($regex, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Map RANCID device types to rConfig device types using mappings from file
     */
    protected function mapDeviceType($rancidType, $deviceName = '')
    {
        $mappingsFile = storage_path('app/rconfig/rancid_mappings.json');

        if (! File::exists($mappingsFile)) {
            return false;
        }

        try {
            $mappings = json_decode(File::get($mappingsFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        $deviceType = strtolower($rancidType);

        if (isset($mappings[$deviceType])) {
            // Replace device name placeholders in prompts
            foreach ($mappings[$deviceType]['prompts'] as $key => $prompt) {
                $mappings[$deviceType]['prompts'][$key] = str_replace('{device_name}', $deviceName, $prompt);
            }

            return $mappings[$deviceType];
        }

        return false;
    }

    /**
     * Ask the user to select a default credential
     */
    protected function askForDefaultCredential()
    {
        $credentials = DeviceCredentials::all();

        if ($credentials->isEmpty()) {
            error('No device credentials found in the system.');
            note('Please create at least one device credential in the rConfig interface before running this import.');
            note('Go to Settings > Credentials to create a credential set first.');

            throw new \Exception('Import cannot continue: No device credentials found');
        }

        info("Select a default credential to use for devices without specific credentials in .cloginrc:");

        $options = [];

        foreach ($credentials as $cred) {
            $displayText = "ID #{$cred->id}: {$cred->cred_name}" .
                (! empty($cred->cred_description) ? " ({$cred->cred_description})" : '');

            $options[$cred->id] = $displayText;
        }

        $credId = select(
            'Which credential would you like to use as default?',
            $options
        );

        info("Using credential #{$credId} as default");

        return $credId;
    }

    /**
     * Display a summary of imported devices
     */
    protected function displaySummary($devices)
    {
        note(count($devices) . ' devices were processed successfully');

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

        // Group by RANCID group
        $byGroup = [];
        foreach ($devices as $device) {
            $group = $device['rancid_group'] ?? 'unknown';
            if (! isset($byGroup[$group])) {
                $byGroup[$group] = 0;
            }
            $byGroup[$group]++;
        }

        info('RANCID group breakdown:');
        foreach ($byGroup as $group => $count) {
            note("- $group: $count devices");
        }

        // Credentials usage
        $withCreds = 0;
        $withCredId = 0;

        foreach ($devices as $device) {
            if (isset($device['device_cred_id']) && $device['device_cred_id'] === 0) {
                $withCreds++;
            } else {
                $withCredId++;
            }
        }

        info('Credentials usage:');
        note("- $withCreds devices with direct credentials (from .cloginrc)");
        note("- $withCredId devices using credential sets");
    }

    /**
     * Display detailed information about the command
     */
    protected function showInfo()
    {
        info('===== rConfig RANCID Import Tool =====');

        note('PURPOSE:');
        $this->line('This command imports devices from RANCID into rConfig.');
        $this->line('It reads RANCID router.db files, extracts device information,');
        $this->line('and creates a JSON file for bulk device import into rConfig.');

        note('PREREQUISITES:');
        $this->line('1. A working RANCID installation');
        $this->line('2. Device type mappings (created with rconfig:rancid-device-mappings)');
        $this->line('3. At least one credential set created in rConfig');

        note('RANCID STRUCTURE:');
        $this->line('RANCID stores data in the following structure:');
        $this->line('  /usr/local/rancid/ (or /var/lib/rancid/)');
        $this->line('    ├── .cloginrc              # Credentials file');
        $this->line('    ├── etc/rancid.conf        # RANCID configuration');
        $this->line('    └── var/                   # Device data');
        $this->line('        ├── groupname/         # Each group is a subdirectory');
        $this->line('        │   ├── router.db      # Device list: hostname:type:status');
        $this->line('        │   └── configs/       # Device configuration files');
        $this->line('');

        note('ROUTER.DB FORMAT:');
        $this->line('The router.db file contains one device per line:');
        $this->line('  hostname:device_type:status');
        $this->line('');
        $this->line('Examples:');
        $this->line('  router1.example.com:cisco:up');
        $this->line('  switch1.example.com:cisco:up');
        $this->line('  fw1.example.com:juniper:up');
        $this->line('  192.168.1.1:cisco:down');

        note('WORKFLOW:');
        $this->line('1. Locate RANCID installation directory');
        $this->line('2. Find all RANCID groups (subdirectories with router.db files)');
        $this->line('3. Parse router.db files and validate devices');
        $this->line('4. Extract credentials from .cloginrc if available');
        $this->line('5. Map device types to rConfig settings');
        $this->line('6. Generate JSON file for import');

        note('USAGE:');
        $this->line('php artisan rconfig:rancid-load-devices');
        $this->line('php artisan rconfig:rancid-load-devices --rancid-base=/usr/local/rancid');
        $this->line('php artisan rconfig:rancid-load-devices --group=networking');
        $this->line('php artisan rconfig:rancid-load-devices --info');

        note('RELATED COMMANDS:');
        $this->line('rconfig:rancid-device-mappings  - Manage device type mappings');
        $this->line('rconfig:rancid-load-devices     - Create rConfig compatible JSON from RANCID');
        $this->line('rconfig:rancid-import-devices   - Import JSON to rConfig database');

        if (confirm('Return to menu?')) {
            $this->showStartMenu();
        }
    }
}