<?php

namespace App\Console\Commands\DataImport;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Template;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class OxidizedToRconfigMappingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:oxidized-device-mappings 
                           {--list : List all current mappings}
                           {--add : Add a new mapping}
                           {--edit= : Edit a specific mapping by device type}
                           {--delete= : Delete a specific mapping by device type}
                           {--info : Show information about how to use this command}';

    protected $description = 'Manage device type mappings for Oxidized import';
    protected $mappingsFile;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->mappingsFile = storage_path('app/rconfig/oxidized_mappings.json');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show info if requested
        if ($this->option('info')) {
            $this->showInfo();

            return;
        }

        // Check if the directory exists, create if it doesn't
        $dir = storage_path('app/rconfig');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Initialize mappings file if it doesn't exist
        if (! File::exists($this->mappingsFile)) {
            File::put($this->mappingsFile, json_encode([], JSON_PRETTY_PRINT));
            $this->info('Created new mappings file: ' . $this->mappingsFile);
        }

        // Handle command options
        if ($this->option('list')) {
            $this->listMappings();
        } elseif ($this->option('add')) {
            $this->addMapping();
        } elseif ($deviceType = $this->option('edit')) {
            $this->editMapping($deviceType);
        } elseif ($deviceType = $this->option('delete')) {
            $this->deleteMapping($deviceType);
        } else {
            // Interactive menu if no options specified
            $freshMappings = $this->loadMappings();
            $this->showMenu($freshMappings);
        }
    }

    /**
     * Load mappings from file
     */
    protected function loadMappings()
    {
        $content = File::get($this->mappingsFile);

        // Check if the file is empty
        if (empty(trim($content))) {
            return [];
        }

        $mappings = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error('Error parsing mappings file: ' . json_last_error_msg());
            note('Initializing with empty mappings.');

            return [];
        }

        return $mappings;
    }

    /**
     * Save mappings to file
     */
    protected function saveMappings($mappings)
    {
        // Ensure the mappings is not empty or null
        if (empty($mappings)) {
            $mappings = [];
        }

        $json = json_encode($mappings, JSON_PRETTY_PRINT);

        // Debug info - remove this in production
        note('Writing to file: ' . $this->mappingsFile);
        note('Content length: ' . strlen($json) . ' bytes');

        File::put($this->mappingsFile, $json);
        info('Mappings saved successfully.');
    }

    /**
     * Display interactive menu
     */
    protected function showMenu()
    {
        note('rConfig Oxidized Device Mappings');

        $action = select(
            'What would you like to do?',
            [
                'info' => 'Show information about this command',
                'list' => 'List all mappings',
                'add' => 'Add a new mapping',
                'edit' => 'Edit a mapping',
                'delete' => 'Delete a mapping',
                'exit' => 'Exit',
            ],
            'info'
        );

        switch ($action) {
            case 'info':
                $this->showInfo();
                break;
            case 'list':
                $this->listMappings();
                break;
            case 'add':
                $this->addMapping();
                break;
            case 'edit':
                $deviceType = $this->askForDeviceType();
                if ($deviceType) {
                    $this->editMapping($deviceType);
                }
                break;
            case 'delete':
                $deviceType = $this->askForDeviceType();
                if ($deviceType) {
                    $this->deleteMapping($deviceType);
                }
                break;
            case 'exit':
                return;
        }

        // Return to menu after action if not exiting
        if ($action !== 'exit') {
            $freshMappings = $this->loadMappings();
            $this->showMenu($freshMappings);
        }
    }

    /**
     * Ask user to select a device type from existing mappings
     */
    protected function askForDeviceType()
    {
        // Always reload mappings from file to ensure we have the latest data
        $freshMappings = $this->loadMappings();

        if (empty($freshMappings)) {
            warning('No mappings exist yet. Add a mapping first.');

            return false;
        }

        // Create options with device types as both keys and values
        $options = [];
        foreach (array_keys($freshMappings) as $deviceType) {
            $options[$deviceType] = $deviceType;
        }

        // Add cancel option
        $options['cancel'] = 'Cancel';

        $selected = select(
            'Select a device type:',
            $options
        );

        if ($selected === 'cancel') {
            return false;
        }

        return $selected;
    }

    /**
     * List all mappings
     */
    protected function listMappings()
    {
        // Always reload mappings directly from file to ensure we have the latest data
        $freshMappings = $this->loadMappings();

        if (empty($freshMappings)) {
            warning('No mappings found in ' . basename($this->mappingsFile));

            return;
        }

        info('Current device type mappings:');

        foreach ($freshMappings as $type => $mapping) {
            note('=== ' . strtoupper($type) . ' ===');
            $this->line('rConfig Type: ' . $mapping['device_type']);
            $this->line('Template ID: ' . $mapping['template_id']);
            $this->line('Vendor ID: ' . $mapping['vendor_id']);
            $this->line('Category ID: ' . $mapping['category_id']);

            // Display prompts
            $this->line('Prompts:');
            foreach ($mapping['prompts'] as $promptName => $promptValue) {
                $this->line("  - $promptName: $promptValue");
            }

            // Display tags
            $this->line('Tags: ' . (is_array($mapping['tags']) ? implode(', ', $mapping['tags']) : $mapping['tags']));
            $this->line(''); // Empty line for spacing
        }

        note(count($freshMappings) . ' mapping(s) found in total.');
    }

    /**
     * Add a new mapping
     */
    protected function addMapping()
    {
        // Always reload mappings from file to ensure we have the latest data
        $freshMappings = $this->loadMappings();

        info('Adding a new device type mapping');

        // Get Oxidized device type
        $deviceType = strtolower(text("Enter the Oxidized device type (e.g. 'ios', 'junos'):"));

        if (isset($freshMappings[$deviceType])) {
            if (! confirm("A mapping for '$deviceType' already exists. Do you want to overwrite it?")) {
                note('Operation cancelled.');

                return;
            }
        }

        // Get available templates, vendors, categories
        $templates = Template::select(['id', 'templateName'])->orderBy('id')->get();
        $vendors = Vendor::select(['id', 'vendorName'])->orderBy('id')->get();
        $categories = Category::select(['id', 'categoryName'])->orderBy('id')->get();

        // Get available tags - NEW!
        $tagOptions = [];

        // Load tags from the database
        $tags = Tag::all(['id', 'tagname']);
        foreach ($tags as $tag) {
            $tagOptions[$tag->id] = $tag->tagname;
        }

        // Display available options
        info('Available Templates:');
        foreach ($templates as $template) {
            $this->line("ID: {$template->id} - {$template->templateName}");
        }

        info('Available Vendors:');
        foreach ($vendors as $vendor) {
            $this->line("ID: {$vendor->id} - {$vendor->vendorName}");
        }

        info('Available Categories:');
        foreach ($categories as $category) {
            $this->line("ID: {$category->id} - {$category->categoryName}");
        }

        // Get mapping details
        $rConfigType = text("Enter the rConfig device type (e.g. 'cisco_ios'):");
        $templateId = text('Enter the template ID:');
        $vendorId = text('Enter the vendor ID:');
        $categoryId = text('Enter the category ID:');

        // Get prompts
        info('Enter prompts (use {device_name} as placeholder for the device name):');
        $prompts = [
            'device_enable_prompt' => ($enablePrompt = text('Enable prompt (default: {device_name}>):', '{device_name}>'))
                ? $enablePrompt : '{device_name}>',
            'device_main_prompt' => ($mainPrompt = text('Main prompt (default: {device_name}#):', '{device_name}#'))
                ? $mainPrompt : '{device_name}#',
        ];

        // Get tags - UPDATED to use multiselect
        $selectedTags = [];

        if (! empty($tagOptions)) {
            // Use multiselect for tags if any tags exist in the database
            note('Select tags for this device type (Space to select, Enter to confirm):');
            $selectedTagIds = multiselect(
                'Choose tags to associate with this device type:',
                $tagOptions
            );

            // Now $selectedTagIds contains an array of tag IDs
            // If no tags were selected, use the first tag
            if (empty($selectedTagIds) && ! empty($tagOptions)) {
                $firstTagId = array_key_first($tagOptions);
                $selectedTags = [$firstTagId];
                note("No tags selected. Automatically using the first tag: {$tagOptions[$firstTagId]}");
            } else {
                $selectedTags = $selectedTagIds;
            }
        } else {
            // If no tags in database, fallback to manual entry
            note('No tags found in the database. Enter tags manually:');
            $tagsInput = text('Enter tags (comma-separated):');
            $selectedTags = array_map('trim', explode(',', $tagsInput));
        }

        // Create mapping in the fresh mappings
        $freshMappings[$deviceType] = [
            'template_id' => (int) $templateId,
            'vendor_id' => (int) $vendorId,
            'category_id' => (int) $categoryId,
            'prompts' => $prompts,
            'tags' => $selectedTags,
            'device_type' => $rConfigType,
        ];

        // Save to file
        $this->saveMappings($freshMappings);

        // Verify the mapping was saved
        $reloadedMappings = $this->loadMappings();
        if (isset($reloadedMappings[$deviceType])) {
            info("Mapping for '$deviceType' added successfully.");
        } else {
            error('Failed to verify mapping was saved. Please check the mappings file.');
        }
    }

    /**
     * Edit an existing mapping
     */
    protected function editMapping($deviceType)
    {
        // Reload mappings from file to ensure we have the latest data
        $freshMappings = $this->loadMappings();

        if (! isset($freshMappings[$deviceType])) {
            error("No mapping found for device type '$deviceType'");

            return;
        }

        info("Editing mapping for '$deviceType'");
        note('Current values shown in [brackets]. Press enter to keep current value.');

        // Get current values from the fresh mappings
        $current = $freshMappings[$deviceType];

        // Update values
        $rConfigType = text("rConfig device type [{$current['device_type']}]:", $current['device_type']);
        $templateId = text("Template ID [{$current['template_id']}]:", $current['template_id']);
        $vendorId = text("Vendor ID [{$current['vendor_id']}]:", $current['vendor_id']);
        $categoryId = text("Category ID [{$current['category_id']}]:", $current['category_id']);

        // Update prompts
        info('Update prompts:');
        $prompts = [];

        foreach ($current['prompts'] as $key => $value) {
            $userInput = text("$key [$value]:", $value);
            $prompts[$key] = ! empty($userInput) ? $userInput : $value;
        }

        // Update tags with multiselect
        $tagOptions = [];
        $tags = \App\Models\Tag::all(['id', 'tagname']);
        foreach ($tags as $tag) {
            $tagOptions[$tag->id] = $tag->tagname;
        }

        if (! empty($tagOptions)) {
            // Convert current tags to IDs if they're strings
            $currentTagIds = [];
            if (isset($current['tags']) && is_array($current['tags'])) {
                // If tags are stored as IDs, use them directly
                if (is_numeric(reset($current['tags']))) {
                    $currentTagIds = $current['tags'];
                } else {
                    // If tags are stored as strings, try to find their IDs
                    foreach ($current['tags'] as $tagName) {
                        $tag = \App\Models\Tag::where('tagname', $tagName)->first();
                        if ($tag) {
                            $currentTagIds[] = $tag->id;
                        }
                    }
                }
            }

            note('Select tags for this device type (Space to select, Enter to confirm):');
            $selectedTagIds = multiselect(
                'Choose tags to associate with this device type:',
                $tagOptions,
                $currentTagIds  // Pre-select current tags if they match IDs
            );

            // If no tags were selected, use the first tag
            if (empty($selectedTagIds) && ! empty($tagOptions)) {
                $firstTagId = array_key_first($tagOptions);
                $selectedTags = [$firstTagId];
                note("No tags selected. Automatically using the first tag: {$tagOptions[$firstTagId]}");
            } else {
                $selectedTags = $selectedTagIds;
            }
        } else {
            // If no tags in database, fallback to manual entry
            $currentTags = is_array($current['tags']) ? implode(', ', $current['tags']) : '';
            $tagsInput = text("Tags (comma-separated) [$currentTags]:", $currentTags);
            $selectedTags = array_map('trim', explode(',', $tagsInput));
        }

        // Update mapping in the fresh mappings
        $freshMappings[$deviceType] = [
            'template_id' => (int) $templateId,
            'vendor_id' => (int) $vendorId,
            'category_id' => (int) $categoryId,
            'prompts' => $prompts,
            'tags' => $selectedTags,
            'device_type' => $rConfigType,
        ];

        // Save to file
        $this->saveMappings($freshMappings);

        // Verify the update was saved
        $verifyMappings = $this->loadMappings();
        if (
            isset($verifyMappings[$deviceType]) &&
            $verifyMappings[$deviceType]['device_type'] === $rConfigType
        ) {
            info("Mapping for '$deviceType' updated successfully.");
        } else {
            error('Failed to verify mapping was updated. Please check the mappings file.');
        }
    }

    /**
     * Delete an existing mapping
     */
    protected function deleteMapping($deviceType)
    {
        // Reload mappings from file to ensure we have the latest data
        $freshMappings = $this->loadMappings();

        if (! isset($freshMappings[$deviceType])) {
            error("No mapping found for device type '$deviceType'");

            return;
        }

        if (confirm("Are you sure you want to delete the mapping for '$deviceType'?")) {
            unset($freshMappings[$deviceType]);
            $this->saveMappings($freshMappings);

            // Verify deletion
            $verifyMappings = $this->loadMappings();
            if (! isset($verifyMappings[$deviceType])) {
                info("Mapping for '$deviceType' deleted successfully.");
            } else {
                error('Failed to delete mapping. Please check the mappings file.');
            }
        } else {
            note('Deletion cancelled.');
        }
    }

    /**
     * Display information about how to use this command
     */
    protected function showInfo()
    {
        $this->line('');
        $this->info('===== rConfig Oxidized Device Mappings =====');
        $this->line('');
        $this->line('This command manages device type mappings for importing devices from Oxidized format.');
        $this->line('');
        $this->info('Purpose:');
        $this->line('The mappings define how Oxidized device types are translated to rConfig settings,');
        $this->line('including template, vendor, category, prompts, and tags.');
        $this->line('');

        $this->info('Mappings File:');
        $this->line('Mappings are stored in: ' . $this->mappingsFile);
        $this->line('');

        $this->info('Usage:');
        $this->line('  php artisan rconfig:oxidized-device-mappings            Interactive menu mode');
        $this->line('  php artisan rconfig:oxidized-device-mappings --list     List all mappings');
        $this->line('  php artisan rconfig:oxidized-device-mappings --add      Add a new mapping');
        $this->line('  php artisan rconfig:oxidized-device-mappings --edit=ios Edit the "ios" mapping');
        $this->line('  php artisan rconfig:oxidized-device-mappings --delete=xos Delete the "xos" mapping');
        $this->line('');

        $this->info('Workflow for Oxidized Import:');
        $this->line('1. Run this command to set up device type mappings');
        $this->line('2. Use the rconfig:oxidized-import command to import devices');
        $this->line('');

        $this->info('Example mapping format:');
        $this->line(json_encode([
            'ios' => [
                'template_id' => 1,
                'vendor_id' => 1,
                'category_id' => 1,
                'prompts' => [
                    'device_enable_prompt' => '{device_name}>',
                    'device_main_prompt' => '{device_name}#',
                ],
                'tags' => ['cisco', 'ios', 'router'],
                'device_type' => 'cisco_ios',
            ],
        ], JSON_PRETTY_PRINT));
        $this->line('');

        $this->info('Prompts:');
        $this->line('Use {device_name} as a placeholder that will be replaced with the actual device name.');
        $this->line('');

        $this->info('Required Database Information:');
        $this->line('You\'ll need the IDs of templates, vendors, and categories from your rConfig installation.');
        $this->line('This command will show available options during the mapping creation process.');
        $this->line('');

        note('RELATED COMMANDS:');
        $this->line('rconfig:oxidized-import-devices - Load devices from Oxidized mapped to devices table');
        $this->line('rconfig:oxidized-load-devices - Create rConfig compatible JSON files from Oxidized hosts');
        $this->line('rconfig:oxidized-device-mappings - Manage device type mappings');
    }
}
