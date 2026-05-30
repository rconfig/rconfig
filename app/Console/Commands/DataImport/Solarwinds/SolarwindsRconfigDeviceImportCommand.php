<?php

namespace App\Console\Commands\DataImport\Solarwinds;

use App\Models\Category;
use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\Template;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class SolarwindsRconfigDeviceImportCommand extends Command
{
    protected $signature = 'rconfig:solarwinds-import-devices
                            {file? : Path to the JSON import file}
                            {--group=1 : Default device group ID to assign devices to}
                            {--dry-run : Validate the import without making changes}
                            {--info : Display information about this command}';
    protected $description = 'Import devices from SolarWinds JSON file to rConfig database';

    public function handle()
    {
        if ($this->option('info') || ! $this->argument('file')) {
            $this->showStartMenu();

            return 0;
        }

        return $this->importDevices();
    }

    protected function showStartMenu()
    {
        note('rConfig SolarWinds Device Import Utility');

        $options = [
            'info' => 'Show information about this command',
            'import' => 'Import devices from a JSON file',
            'latest' => 'Import from the latest available JSON file',
            'select' => 'Select from all available import files',
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
                $filePath = text('Enter the path to the JSON import file:');
                if (empty($filePath)) {
                    error('No file path provided. Operation cancelled.');

                    return;
                }
                $this->input->setArgument('file', $filePath);
                $this->importDevices();
                break;
            case 'latest':
                $latestFile = $this->findLatestImportFile();
                if ($latestFile) {
                    $this->input->setArgument('file', $latestFile);
                    $this->importDevices();
                } else {
                    error('No import files found.');
                }
                break;
            case 'select':
                $selectedFile = $this->selectImportFile();
                if ($selectedFile) {
                    $this->input->setArgument('file', $selectedFile);
                    $this->importDevices();
                }
                break;
            case 'exit':
                info('Goodbye!');

                return;
        }
    }

    protected function showInfo()
    {
        info('===== rConfig SolarWinds Device Import Tool =====');

        note('PURPOSE:');
        $this->line('This command imports devices from a JSON file (created by rconfig:solarwinds-load-devices)');
        $this->line('into the rConfig database. It validates device data and creates all necessary');
        $this->line('relationships with templates, vendors, categories, and credentials.');

        note('PREREQUISITES:');
        $this->line('1. A valid JSON import file (created by rconfig:solarwinds-load-devices)');
        $this->line('2. Existing templates, vendors, and categories in the rConfig database');
        $this->line('3. Valid credential sets for the devices');

        note('FEATURES:');
        $this->line('- Can import from a specific file or find the latest one automatically');
        $this->line('- Validates all data before import');
        $this->line('- Supports dry-run mode to check for issues without making changes');
        $this->line('- Automatically assigns devices to groups');
        $this->line('- Creates device-tag relationships including custom property tags');
        $this->line('- Skips devices that already exist (based on name or IP)');

        note('JSON FORMAT:');
        $this->line('The JSON file should contain an array of device objects with:');
        $this->line('- device_name: The device hostname');
        $this->line('- device_ip: IP address');
        $this->line('- device_model: Device model/type');
        $this->line('- template_id: Valid template ID');
        $this->line('- vendor_id: Valid vendor ID');
        $this->line('- device_category_id: Valid category ID');
        $this->line('- device_cred_id: Credential set ID');
        $this->line('- prompts: Object with device_enable_prompt and device_main_prompt');
        $this->line('- tags: Array of tag IDs');
        $this->line('- solarwinds_machine_type: Original SolarWinds machine type (for reference)');
        $this->line('- solarwinds_node_groups: Original SolarWinds node groups (for reference)');

        note('USAGE:');
        $this->line('php artisan rconfig:solarwinds-import-devices /path/to/file.json');
        $this->line('php artisan rconfig:solarwinds-import-devices --latest');
        $this->line('php artisan rconfig:solarwinds-import-devices --dry-run /path/to/file.json');
        $this->line('php artisan rconfig:solarwinds-import-devices --group=2 /path/to/file.json');

        note('RELATED COMMANDS:');
        $this->line('rconfig:solarwinds-connection       - Manage SolarWinds API connection');
        $this->line('rconfig:solarwinds-device-mappings  - Manage device type mappings');
        $this->line('rconfig:solarwinds-load-devices     - Load devices from SolarWinds');
        $this->line('rconfig:solarwinds-import-devices   - Import devices to rConfig');

        if (confirm('Return to menu?')) {
            $this->showStartMenu();
        }
    }

    protected function findLatestImportFile()
    {
        $path = storage_path('app/rconfig/tempdir');
        $files = glob($path . '/rconfig_import_*.json');

        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files[0];
    }

    protected function selectImportFile()
    {
        $path = storage_path('app/rconfig/tempdir');
        $files = glob($path . '/rconfig_import_*.json');

        if (empty($files)) {
            error('No import files found.');

            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $options = [];
        foreach ($files as $file) {
            $modTime = date('Y-m-d H:i:s', filemtime($file));
            $fileName = basename($file);
            $size = round(filesize($file) / 1024, 2) . ' KB';

            $deviceCount = '?';
            try {
                $data = json_decode(File::get($file), true);
                if (is_array($data)) {
                    $deviceCount = count($data);
                }
            } catch (\Exception $e) {
                // Silently ignore
            }

            $options[$file] = "$fileName ($modTime, $size, $deviceCount devices)";
        }

        $options['cancel'] = 'Cancel selection';

        $selected = select(
            'Select an import file:',
            $options,
            null,
            8
        );

        if ($selected === 'cancel') {
            note('File selection cancelled.');

            return null;
        }

        note('Selected: ' . basename($selected));

        return $selected;
    }

    protected function importDevices()
    {
        $inputFile = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $defaultGroupId = $this->option('group');

        if (! File::exists($inputFile)) {
            error("Input file not found: $inputFile");

            return 1;
        }

        try {
            $devices = json_decode(File::get($inputFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error('Error parsing JSON file: ' . json_last_error_msg());

                return 1;
            }

            if (! is_array($devices)) {
                error('Invalid JSON file format - expected an array of devices');

                return 1;
            }

            if (empty($devices)) {
                warning('No devices found in the import file');

                return 1;
            }
        } catch (\Exception $e) {
            error('Error reading file: ' . $e->getMessage());

            return 1;
        }

        info('Found ' . count($devices) . ' devices in the import file');

        if ($isDryRun) {
            note('Running in dry-run mode - no database changes will be made');
        }

        // Validate device data before import
        $validDevices = [];
        $invalidDevices = [];

        $bar = progress('Validating devices', count($devices));

        foreach ($devices as $index => $device) {
            $validationResult = $this->validateDevice($device, $index);

            if ($validationResult === true) {
                $validDevices[] = $device;
            } else {
                $invalidDevices[$device['device_name'] ?? "Unknown-$index"] = $validationResult;
            }

            $bar->advance();
        }

        $bar->finish();

        // Report validation results
        if (! empty($invalidDevices)) {
            warning(count($invalidDevices) . ' devices failed validation:');

            foreach ($invalidDevices as $device_name => $errors) {
                $this->line("Device: $device_name");
                foreach ($errors as $error) {
                    $this->line("  - $error");
                }
            }

            if (! confirm('Continue with ' . count($validDevices) . ' valid devices?')) {
                note('Import cancelled by user');

                return 1;
            }
        }

        if ($isDryRun) {
            info('Dry run completed. ' . count($validDevices) . ' devices would be imported.');

            return 0;
        }

        // Import valid devices to database
        $importedCount = 0;
        $skipCount = 0;

        if (empty($validDevices)) {
            warning('No valid devices to import.');

            return 1;
        }

        DB::beginTransaction();

        try {
            $bar = progress('Importing devices', count($validDevices));

            foreach ($validDevices as $deviceData) {
                // Check if device already exists
                $existingDevice = Device::where('device_name', $deviceData['device_name'])
                    ->orWhere('device_ip', $deviceData['device_ip'])
                    ->first();

                if ($existingDevice) {
                    $skipCount++;
                    $bar->advance();
                    continue;
                }

                // Create new device
                $device = new Device;
                $device->device_name = $deviceData['device_name'];
                $device->device_ip = $deviceData['device_ip'];
                $device->device_main_prompt = $deviceData['prompts']['device_main_prompt'];
                $device->device_enable_prompt = $deviceData['prompts']['device_enable_prompt'];
                $device->device_model = $deviceData['device_model'];
                $device->device_template = $deviceData['template_id'];
                $device->device_category_id = $deviceData['device_category_id'];
                $device->device_cred_id = $deviceData['device_cred_id'];
                $device->status = 1;

                $device->save();

                // Add relationships
                $device->Tag()->attach($deviceData['tags'] ?? []);
                $device->Vendor()->attach($deviceData['vendor_id']);
                $device->Category()->attach($deviceData['device_category_id']);
                $device->Template()->attach($deviceData['template_id']);

                $importedCount++;
                $bar->advance();
            }

            $bar->finish();

            DB::commit();

            info('Import completed successfully!');
            note("Imported: $importedCount devices");

            if ($skipCount > 0) {
                note("Skipped: $skipCount devices (already exist)");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            error('Import failed: ' . $e->getMessage());
            Log::error('SolarWinds device import failed: ' . $e->getMessage());

            return 1;
        }

        if (confirm('Return to main menu?')) {
            $this->showStartMenu();
        }

        return 0;
    }

    protected function validateDevice($device, $index)
    {
        $errors = [];

        // Check required fields
        $requiredFields = [
            'device_name' => 'Device name',
            'device_ip' => 'IP address',
            'device_model' => 'Device model',
            'template_id' => 'Template ID',
            'vendor_id' => 'Vendor ID',
            'device_category_id' => 'Category ID',
            'device_cred_id' => 'Credential ID',
        ];

        foreach ($requiredFields as $field => $label) {
            if (! isset($device[$field]) || (empty($device[$field]) && $device[$field] !== 0)) {
                $errors[] = "$label is missing";
            }
        }

        // Check if device already exists
        if (isset($device['device_name'])) {
            $existingDevice = Device::where('device_name', $device['device_name'])
                ->orWhere('device_ip', $device['device_ip'] ?? '')
                ->first();

            if ($existingDevice) {
                $errors[] = "Device with same name or IP already exists (ID: {$existingDevice->id})";
            }
        }

        // Validate references to other models
        if (isset($device['template_id']) && is_numeric($device['template_id'])) {
            $template = Template::find($device['template_id']);
            if (! $template) {
                $errors[] = "Template ID {$device['template_id']} not found";
            }
        }

        if (isset($device['vendor_id']) && is_numeric($device['vendor_id'])) {
            $vendor = Vendor::find($device['vendor_id']);
            if (! $vendor) {
                $errors[] = "Vendor ID {$device['vendor_id']} not found";
            }
        }

        if (isset($device['device_category_id']) && is_numeric($device['device_category_id'])) {
            $category = Category::find($device['device_category_id']);
            if (! $category) {
                $errors[] = "Category ID {$device['device_category_id']} not found";
            }
        }

        if (isset($device['device_cred_id']) && is_numeric($device['device_cred_id']) && $device['device_cred_id'] > 0) {
            $credential = DeviceCredentials::find($device['device_cred_id']);
            if (! $credential) {
                $errors[] = "Credential set ID {$device['device_cred_id']} not found";
            }
        }

        // Validate prompts
        if (! isset($device['prompts']) || ! is_array($device['prompts'])) {
            $errors[] = 'Prompts missing or invalid';
        } else {
            if (! isset($device['prompts']['device_main_prompt']) || empty($device['prompts']['device_main_prompt'])) {
                $errors[] = 'Main prompt is required';
            }
            if (! isset($device['prompts']['device_enable_prompt']) || empty($device['prompts']['device_enable_prompt'])) {
                $errors[] = 'Enable prompt is required';
            }
        }

        // Validate IP address
        if (isset($device['device_ip'])) {
            if (! filter_var($device['device_ip'], FILTER_VALIDATE_IP)) {
                $errors[] = "Invalid IP address: {$device['device_ip']}";
            }
        }

        return empty($errors) ? true : $errors;
    }
}
