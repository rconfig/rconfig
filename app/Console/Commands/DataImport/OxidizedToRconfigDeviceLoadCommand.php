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

class OxidizedToRconfigDeviceLoadCommand extends Command
{
    protected $signature = 'rconfig:oxidized-load-devices
                            {file? : Path to the oxidized hosts file}
                            {--info : Display information about this command}';
    protected $description = 'Import devices from Oxidized to rConfig';

    /**
     * Storage for selected credential ID to use as default
     */
    protected $defaultCredentialId = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show info or menu if no file is provided
        if ($this->option('info') || ! $this->argument('file')) {
            $this->showStartMenu();

            return 0;
        }

        return $this->ImportFromoxidized();
    }

    /**
     * Show the start menu with options
     */
    protected function showStartMenu()
    {
        note('rConfig Oxidized Import Utility');

        $options = [
            'info' => 'Show information about this command',
            'import' => 'Import devices from an Oxidized file',
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
                $filePath = text('Enter the path to the Oxidized file:');
                if (empty($filePath)) {
                    error('No file path provided. Operation cancelled.');

                    return;
                }

                $this->input->setArgument('file', $filePath);
                $this->ImportFromoxidized();
                break;
            case 'exit':
                info('Goodbye!');

                return;
        }
    }

    /**
     * Main import process
     */
    protected function ImportFromoxidized()
    {
        $inputFile = $this->argument('file');
        $mappingsFile = storage_path('app/rconfig/oxidized_mappings.json');

        // Check for prerequisites first
        if (! File::exists($inputFile)) {
            error("Input file not found: $inputFile");

            return 1;
        }

        // Check if mappings file exists before proceeding
        if (! File::exists($mappingsFile)) {
            error('Device type mappings file not found!');
            note('The mappings file is required to translate Oxidized device types to rConfig settings.');
            note('You need to create device type mappings before importing devices.');

            if (confirm('Would you like to create device mappings now?')) {
                $this->call('rconfig:oxidized-device-mappings');

                // Check again if the file was created
                if (! File::exists($mappingsFile)) {
                    error("Mappings file still doesn't exist. Please create at least one device mapping.");

                    return 1;
                }

                note('Mappings created successfully. Continuing with import...');
            } else {
                note("Please run 'php artisan rconfig:oxidized-device-mappings' to create mappings first.");

                return 1;
            }
        }

        // Create a timestamped copy of the input file
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $tmpFile = storage_path("app/rconfig/tempdir/oxidized_import_{$timestamp}.tmp");
        $failuresFile = storage_path("app/rconfig/tempdir/oxidized_import_failures_{$timestamp}.txt");

        // Create directory if it doesn't exist
        $dir = dirname($tmpFile);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::copy($inputFile, $tmpFile);

        info('Starting import process from Oxidized to rConfig');
        note('Temporary file created: ' . basename($tmpFile));
        note('Failures will be logged to: ' . basename($failuresFile));

        // Read the content of the copied file
        $lines = file($tmpFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $validDevices = [];
        $failures = [];

        info('Validating ' . count($lines) . ' devices...');

        // Process devices with progress tracking
        info('Validating ' . count($lines) . ' devices...');

        $validatedCount = 0;
        $totalCount = count($lines);

        $bar = progress('Validating devices', $totalCount);

        foreach ($lines as $lineIndex => $line) {
            $parts = explode(':', $line);
            $hostname = $parts[0];
            $deviceType = isset($parts[1]) ? $parts[1] : null;

            // First check if we have a valid device type mapping
            if ($deviceType && ! $this->mapDeviceType($deviceType, $hostname)) {
                $failures[] = $line . " # Unknown device type '" . $deviceType . "'. A mapping has not been found for this device type.";
                $bar->advance();

                continue;
            }

            // Check if device is reachable
            $result = $this->checkDeviceReachability($hostname);

            if ($result['success']) {
                // Add to valid devices
                $deviceInfo = [
                    'device_name' => $hostname,
                    'device_ip' => $result['ip'],
                    'device_type' => $deviceType,
                    'username' => isset($parts[2]) ? $parts[2] : null,
                    'password' => isset($parts[3]) ? $parts[3] : null,
                    'enable_password' => isset($parts[4]) ? $parts[4] : null,
                ];

                $validDevices[] = $deviceInfo;
            } else {
                $failures[] = $line . ' # ' . $result['error'];
            }

            $bar->advance();
        }

        $bar->finish();

        // Log failures
        if (! empty($failures)) {
            File::put($failuresFile, implode("\n", $failures));
            warning(count($failures) . ' devices failed validation. See ' . basename($failuresFile));
        }

        // Update the tmp file to include only valid devices
        $validLines = array_map(function ($device) {
            return $device['device_name'] . ':' . $device['device_type'] .
                (isset($device['username']) ? ':' . $device['username'] : '') .
                (isset($device['password']) ? ':' . $device['password'] : '') .
                (isset($device['enable_password']) ? ':' . $device['enable_password'] : '');
        }, $validDevices);

        File::put($tmpFile, implode("\n", $validLines));

        // Process valid devices
        info('Processing ' . count($validDevices) . ' valid devices...');

        $outputData = [];
        $processingBar = progress('Processing devices', count($validDevices));

        foreach ($validDevices as $device) {
            note('Processing ' . $device['device_name']);

            // Process device based on type
            $outputData[] = $this->processDevice($device);

            $processingBar->advance();
        }

        $processingBar->finish();

        // Save the output data
        $outputFile = storage_path("app/rconfig/tempdir/rconfig_import_{$timestamp}.json");
        File::put($outputFile, json_encode($outputData, JSON_PRETTY_PRINT));

        info('Import process completed successfully!');
        note('Generated rConfig compatible data: ' . basename($outputFile));

        if (confirm('Would you like to view a summary of the imported devices?')) {
            $this->displaySummary($outputData);
        }

        // Ask to return to menu
        if (confirm('Return to main menu?')) {
            $this->showStartMenu();
        }

        return 0;
    }

    /**
     * Display a summary of imported devices
     */
    protected function displaySummary($devices)
    {
        note(count($devices) . ' devices were imported successfully');

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

        // Group by credential usage
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
        note("- $withCreds devices with direct credentials");
        note("- $withCredId devices using credential sets");
    }

    /**
     * Check if a device is reachable (DNS resolution and SSH connectivity)
     *
     * @param  string  $hostname  The hostname to check
     * @return array Result with success status, IP (if successful), and error message (if failed)
     */
    protected function checkDeviceReachability($hostname)
    {
        // Try to resolve hostname
        $ip = gethostbyname($hostname);

        // Check if resolvable (if gethostbyname fails, it returns the original hostname)
        if ($ip === $hostname) {
            return [
                'success' => false,
                'error' => 'Failed to resolve hostname',
                'ip' => null,
            ];
        }

        // Check if reachable via SSH (port 22)
        $connection = @fsockopen($ip, 22, $errNo, $errStr, 5);
        if (! $connection) {
            return [
                'success' => false,
                'error' => "Failed to connect to SSH (port 22): $errStr",
                'ip' => $ip,
            ];
        }
        fclose($connection);

        // All checks passed
        return [
            'success' => true,
            'error' => null,
            'ip' => $ip,
        ];
    }

    /**
     * Process a single device based on its type
     */
    protected function processDevice($device)
    {
        $deviceMapping = $this->mapDeviceType($device['device_type'], $device['device_name']);

        // This should never happen if we filtered properly in the handle method,
        // but adding as a safety check
        if (! $deviceMapping) {
            throw new \Exception('Unsupported device type. A mapping has not been found: ' . $device['device_type']);
        }

        $output = [
            'device_name' => $device['device_name'],
            'device_ip' => $device['device_ip'],
            'device_model' => $deviceMapping['device_type'],
            'template_id' => $deviceMapping['template_id'],
            'vendor_id' => $deviceMapping['vendor_id'],
            'device_category_id' => $deviceMapping['category_id'],
            'prompts' => $deviceMapping['prompts'],
            'tags' => $deviceMapping['tags'],
        ];

        // Check if device has specific credentials in the input file
        if (! empty($device['username']) && ! empty($device['password'])) {
            // Use device-specific credentials directly on the device
            $output['device_cred_id'] = 0; // Indicates using device-level credentials
            $output['device_username'] = $device['username'];
            $output['device_password'] = $device['password'];

            // Add enable password if present
            if (! empty($device['enable_password'])) {
                $output['device_enable_password'] = $device['enable_password'];
            }
        } else {
            // If no specific credentials, use the default credential
            if ($this->defaultCredentialId === null) {
                // Ask for default credentials to use
                $this->defaultCredentialId = $this->askForDefaultCredential();
            }

            $output['device_cred_id'] = $this->defaultCredentialId;
        }

        // Add connection information based on device type
        $output['connection_type'] = 'ssh'; // Default to SSH
        $output['port'] = 22;  // Default SSH port

        return $output;
    }

    /**
     * Map Oxidized device types to rConfig device types using mappings from a file
     *
     * @param  string  $oxidizedType  The device type from Oxidized
     * @param  string  $deviceName  The device name for building prompts
     * @return array|false Complete device mapping information or false if mapping failed
     */
    protected function mapDeviceType($oxidizedType, $deviceName = '')
    {
        // Define the path to mappings file
        $mappingsFile = storage_path('app/rconfig/oxidized_mappings.json');

        // Check if mappings file exists - this should have been caught earlier,
        // but adding this check as a safety measure
        if (! File::exists($mappingsFile)) {
            return false;
        }

        // Load mappings from file
        try {
            $mappings = json_decode(File::get($mappingsFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                warning('Error parsing mappings file: ' . json_last_error_msg());

                return false;
            }
        } catch (\Exception $e) {
            warning('Error reading mappings file: ' . $e->getMessage());

            return false;
        }

        // Look up the device type in our mappings
        $deviceType = strtolower($oxidizedType);

        if (isset($mappings[$deviceType])) {
            // Replace device name placeholders in prompts
            foreach ($mappings[$deviceType]['prompts'] as $key => $prompt) {
                $mappings[$deviceType]['prompts'][$key] = str_replace('{device_name}', $deviceName, $prompt);
            }

            return $mappings[$deviceType];
        }

        // Return false to indicate mapping failure
        return false;
    }

    /**
     * Ask the user to select a default credential from the existing ones
     * This will be applied to all devices without a specific username/password in the Oxidized file
     *
     * @return int The selected credential ID
     */
    protected function askForDefaultCredential()
    {
        $credentials = DeviceCredentials::all();

        if ($credentials->isEmpty()) {
            error('No device credentials found in the system.');
            note('Please create at least one device credential in the rConfig interface before running this import.');
            note('Go to Settings > Credentials to create a credential set first.');

            // Throw an exception to halt execution
            throw new \Exception('Import cannot continue: No device credentials found');
        }

        // Display a header explaining what this selection is for
        info("Select a default credential to use for all devices that don't have specific credentials in the Oxidized file:");

        // Build options for the select prompt
        $options = [];

        foreach ($credentials as $cred) {
            $displayText = "ID #{$cred->id}: {$cred->cred_name}" .
                (! empty($cred->cred_description) ? " ({$cred->cred_description})" : '');

            $options[$cred->id] = $displayText;
        }

        // Use Laravel Prompts select
        $credId = select(
            'Which credential would you like to use as default?',
            $options
        );

        info("Using credential #{$credId} as default for devices without specific credentials");

        return $credId;
    }

    /**
     * Display detailed information about the command
     */
    protected function showInfo()
    {
        info('===== rConfig Oxidized Import Tool =====');

        note('PURPOSE:');
        $this->line('This command imports devices from an Oxidized format file into rConfig.');
        $this->line('It validates device connectivity, maps device types, and creates a JSON file');
        $this->line('that can be used for bulk device imports in rConfig.');

        note('PREREQUISITES:');
        $this->line('1. A valid Oxidized hosts file');
        $this->line('2. Device type mappings (created with rconfig:oxidized-device-mappings)');
        $this->line('3. At least one credential set created in rConfig');

        note('OXIDISED FILE FORMAT:');
        $this->line('The Oxidized file should contain one device per line in the following format:');
        $this->line('');
        $this->line('hostname:device_type[:username:password[:enable_password]]');
        $this->line('');
        $this->line('Examples:');
        $this->line('router1.local:ios');
        $this->line('switch2.local:ios:admin:cisco123');
        $this->line('fw1.local:asa:admin:cisco123:enable_pw');
        $this->line('');
        $this->line('Fields:');
        $this->line('- hostname: The device hostname (must be DNS resolvable)');
        $this->line('- device_type: The Oxidized device type (must be mapped in rConfig)');
        $this->line('- username: (Optional) Device-specific username');
        $this->line('- password: (Optional) Device-specific password');
        $this->line('- enable_password: (Optional) Device-specific enable password');
        $this->line('');
        $this->line('If username and password are omitted, you\'ll be prompted to select');
        $this->line('a default credential set to use for those devices.');

        note('WORKFLOW:');
        $this->line('1. Validate device connectivity and DNS resolution');
        $this->line('2. Map Oxidized device types to rConfig device types');
        $this->line('3. Generate a JSON file for import into rConfig');
        $this->line('4. Log any devices that failed validation');

        note('USAGE:');
        $this->line('php artisan rconfig:oxidized-device-mappings /path/to/oxidized/file.txt');
        $this->line('php artisan rconfig:oxidized-device-mappings --info');

        note('RELATED COMMANDS:');
        $this->line('rconfig:oxidized-import-devices - Load devices from Oxidized mapped to devices table');
        $this->line('rconfig:oxidized-load-devices - Create rConfig compatible JSON files from Oxidized hosts');
        $this->line('rconfig:oxidized-device-mappings - Manage device type mappings');

        if (confirm('Return to menu?')) {
            $this->showStartMenu();
        }
    }
}
