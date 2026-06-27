<?php

namespace App\Console\Commands\DataImport\NetMri;

use App\Models\Category;
use App\Models\DeviceCredentials;
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

class NetMriToRconfigMappingsCommand extends Command
{
    protected $signature = 'rconfig:netmri-device-mappings 
                           {--list : List all current mappings}
                           {--add : Add a new mapping}
                           {--edit= : Edit a specific mapping by device type}
                           {--delete= : Delete a specific mapping by device type}
                           {--info : Show information about how to use this command}';
    protected $description = 'Manage device type mappings for NetMRI import';
    protected $mappingsFile;

    public function __construct()
    {
        parent::__construct();
        $this->mappingsFile = storage_path('app/rconfig/netmri_mappings.json');
    }

    public function handle()
    {
        if ($this->option('info')) {
            $this->showInfo();

            return;
        }

        $dir = storage_path('app/rconfig');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (! File::exists($this->mappingsFile)) {
            File::put($this->mappingsFile, json_encode([], JSON_PRETTY_PRINT));
            info('Created new mappings file: ' . $this->mappingsFile);
        }

        if ($this->option('list')) {
            $this->listMappings();
        } elseif ($this->option('add')) {
            $this->addMapping();
        } elseif ($deviceType = $this->option('edit')) {
            $this->editMapping($deviceType);
        } elseif ($deviceType = $this->option('delete')) {
            $this->deleteMapping($deviceType);
        } else {
            $this->showMenu();
        }
    }

    protected function loadMappings()
    {
        $content = File::get($this->mappingsFile);

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

    protected function saveMappings($mappings)
    {
        if (empty($mappings)) {
            $mappings = [];
        }

        $json = json_encode($mappings, JSON_PRETTY_PRINT);
        File::put($this->mappingsFile, $json);
        info('Mappings saved successfully.');
    }

    protected function showMenu()
    {
        note('rConfig NetMRI Device Mappings');

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

        if ($action !== 'exit') {
            $this->showMenu();
        }
    }

    protected function askForDeviceType()
    {
        $freshMappings = $this->loadMappings();

        if (empty($freshMappings)) {
            warning('No mappings exist yet. Add a mapping first.');

            return false;
        }

        $options = [];
        foreach (array_keys($freshMappings) as $deviceType) {
            $options[$deviceType] = $deviceType;
        }

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

    protected function listMappings()
    {
        $freshMappings = $this->loadMappings();

        if (empty($freshMappings)) {
            warning('No mappings found in ' . basename($this->mappingsFile));

            return;
        }

        info('Current NetMRI device type mappings:');

        foreach ($freshMappings as $type => $mapping) {
            note('=== ' . strtoupper($type) . ' ===');
            $this->line('rConfig Type: ' . $mapping['device_type']);
            $this->line('Template ID: ' . $mapping['template_id']);
            $this->line('Vendor ID: ' . $mapping['vendor_id']);
            $this->line('Category ID: ' . $mapping['category_id']);
            $this->line('Credential ID: ' . $mapping['credential_id']);

            if (isset($mapping['default_group_id']) && $mapping['default_group_id']) {
                $this->line('Default Group ID: ' . $mapping['default_group_id']);
            }

            $this->line('Prompts:');
            foreach ($mapping['prompts'] as $promptName => $promptValue) {
                $this->line("  - $promptName: $promptValue");
            }

            $this->line('Tags: ' . (is_array($mapping['tags']) ? implode(', ', $mapping['tags']) : $mapping['tags']));

            if (! empty($mapping['site_zone_tag_mapping'])) {
                $this->line('Site/Zone Tag Mapping:');
                foreach ($mapping['site_zone_tag_mapping'] as $key => $value) {
                    $this->line("  - $key: Tag ID $value");
                }
            }

            $this->line('');
        }

        note(count($freshMappings) . ' mapping(s) found in total.');
    }

    protected function addMapping()
    {
        $freshMappings = $this->loadMappings();

        info('Adding a new NetMRI device type mapping');

        $deviceType = strtolower(text("Enter the NetMRI device type (e.g. 'cisco-ios', 'juniper-junos'):"));

        if (isset($freshMappings[$deviceType])) {
            if (! confirm("A mapping for '$deviceType' already exists. Do you want to overwrite it?")) {
                note('Operation cancelled.');

                return;
            }
        }

        // Get available resources
        $templates = Template::select(['id', 'templateName'])->orderBy('id')->get();
        $vendors = Vendor::select(['id', 'vendorName'])->orderBy('id')->get();
        $categories = Category::select(['id', 'categoryName'])->orderBy('id')->get();
        $credentials = DeviceCredentials::select(['id', 'cred_name', 'cred_description'])->orderBy('id')->get();

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

        info('Available Credential Sets:');
        foreach ($credentials as $cred) {
            $desc = $cred->cred_description ? " ({$cred->cred_description})" : '';
            $this->line("ID: {$cred->id} - {$cred->cred_name}{$desc}");
        }

        // Get mapping details
        $rConfigType = text("Enter the rConfig device type (e.g. 'cisco_ios'):");
        $templateId = text('Enter the template ID:');
        $vendorId = text('Enter the vendor ID:');
        $categoryId = text('Enter the category ID:');
        $credentialId = text('Enter the credential set ID (devices will use this by default):');

        $defaultGroupId = text('Enter the default device group ID (optional, leave empty for none):');
        if (empty($defaultGroupId)) {
            $defaultGroupId = null;
        }

        // Get prompts
        info('Enter prompts (use {device_name} as placeholder for the device name):');
        $prompts = [
            'device_enable_prompt' => ($enablePrompt = text('Enable prompt (default: {device_name}>):', '{device_name}>'))
                ? $enablePrompt : '{device_name}>',
            'device_main_prompt' => ($mainPrompt = text('Main prompt (default: {device_name}#):', '{device_name}#'))
                ? $mainPrompt : '{device_name}#',
        ];

        // Get tags
        $tagOptions = [];
        $tags = Tag::all(['id', 'tagname']);
        foreach ($tags as $tag) {
            $tagOptions[$tag->id] = $tag->tagname;
        }

        $selectedTags = [];

        if (! empty($tagOptions)) {
            note('Select tags for this device type (Space to select, Enter to confirm):');
            $selectedTagIds = multiselect(
                'Choose tags to associate with this device type:',
                $tagOptions
            );

            if (empty($selectedTagIds)) {
                $firstTagId = array_key_first($tagOptions);
                $selectedTags = [$firstTagId];
                note("No tags selected. Automatically using the first tag: {$tagOptions[$firstTagId]}");
            } else {
                $selectedTags = $selectedTagIds;
            }
        } else {
            note('No tags found in the database. Enter tags manually:');
            $tagsInput = text('Enter tags (comma-separated):');
            $selectedTags = array_map('trim', explode(',', $tagsInput));
        }

        // Ask about site/zone tag mapping
        $siteZoneMapping = [];
        if (confirm('Do you want to map NetMRI sites/zones to additional tags?', false)) {
            note('You can map NetMRI site or zone names to specific tag IDs.');
            note('Example: Map "DMZ" zone to a "DMZ" tag ID');

            $addMore = true;
            while ($addMore) {
                $siteName = text('Enter NetMRI site or zone name (e.g., "DMZ", "DC-East"):');
                $tagId = text('Enter tag ID to assign to devices in this site/zone:');

                if ($siteName && $tagId) {
                    $siteZoneMapping[$siteName] = (int) $tagId;
                }

                $addMore = confirm('Add another site/zone mapping?', false);
            }
        }

        // Create mapping
        $freshMappings[$deviceType] = [
            'template_id' => (int) $templateId,
            'vendor_id' => (int) $vendorId,
            'category_id' => (int) $categoryId,
            'credential_id' => (int) $credentialId,
            'default_group_id' => $defaultGroupId ? (int) $defaultGroupId : null,
            'prompts' => $prompts,
            'tags' => $selectedTags,
            'device_type' => $rConfigType,
            'site_zone_tag_mapping' => $siteZoneMapping,
        ];

        $this->saveMappings($freshMappings);

        $reloadedMappings = $this->loadMappings();
        if (isset($reloadedMappings[$deviceType])) {
            info("Mapping for '$deviceType' added successfully.");
        } else {
            error('Failed to verify mapping was saved. Please check the mappings file.');
        }
    }

    protected function editMapping($deviceType)
    {
        $freshMappings = $this->loadMappings();

        if (! isset($freshMappings[$deviceType])) {
            error("No mapping found for device type '$deviceType'");

            return;
        }

        info("Editing mapping for '$deviceType'");
        note('Current values shown in [brackets]. Press enter to keep current value.');

        $current = $freshMappings[$deviceType];

        $rConfigType = text("rConfig device type [{$current['device_type']}]:", $current['device_type']);
        $templateId = text("Template ID [{$current['template_id']}]:", $current['template_id']);
        $vendorId = text("Vendor ID [{$current['vendor_id']}]:", $current['vendor_id']);
        $categoryId = text("Category ID [{$current['category_id']}]:", $current['category_id']);
        $credentialId = text("Credential ID [{$current['credential_id']}]:", $current['credential_id']);

        $currentGroupId = $current['default_group_id'] ?? 'none';
        $defaultGroupId = text("Default Group ID [$currentGroupId]:", $currentGroupId);
        if ($defaultGroupId === 'none' || empty($defaultGroupId)) {
            $defaultGroupId = null;
        }

        // Update prompts
        info('Update prompts:');
        $prompts = [];
        foreach ($current['prompts'] as $key => $value) {
            $userInput = text("$key [$value]:", $value);
            $prompts[$key] = ! empty($userInput) ? $userInput : $value;
        }

        // Update tags
        $tagOptions = [];
        $tags = Tag::all(['id', 'tagname']);
        foreach ($tags as $tag) {
            $tagOptions[$tag->id] = $tag->tagname;
        }

        if (! empty($tagOptions)) {
            $currentTagIds = [];
            if (isset($current['tags']) && is_array($current['tags'])) {
                if (is_numeric(reset($current['tags']))) {
                    $currentTagIds = $current['tags'];
                } else {
                    foreach ($current['tags'] as $tagName) {
                        $tag = Tag::where('tagname', $tagName)->first();
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
                $currentTagIds
            );

            if (empty($selectedTagIds)) {
                $firstTagId = array_key_first($tagOptions);
                $selectedTags = [$firstTagId];
                note("No tags selected. Automatically using the first tag: {$tagOptions[$firstTagId]}");
            } else {
                $selectedTags = $selectedTagIds;
            }
        } else {
            $currentTags = is_array($current['tags']) ? implode(', ', $current['tags']) : '';
            $tagsInput = text("Tags (comma-separated) [$currentTags]:", $currentTags);
            $selectedTags = array_map('trim', explode(',', $tagsInput));
        }

        // Update site/zone mapping
        $siteZoneMapping = $current['site_zone_tag_mapping'] ?? [];
        if (confirm('Edit site/zone tag mappings?', false)) {
            if (! empty($siteZoneMapping)) {
                info('Current mappings:');
                foreach ($siteZoneMapping as $name => $tagId) {
                    $this->line("  $name => Tag ID $tagId");
                }
            }

            if (confirm('Clear existing mappings and start fresh?', false)) {
                $siteZoneMapping = [];
            }

            $addMore = confirm('Add site/zone mappings?', false);
            while ($addMore) {
                $siteName = text('Enter NetMRI site or zone name:');
                $tagId = text('Enter tag ID:');

                if ($siteName && $tagId) {
                    $siteZoneMapping[$siteName] = (int) $tagId;
                }

                $addMore = confirm('Add another?', false);
            }
        }

        // Update mapping
        $freshMappings[$deviceType] = [
            'template_id' => (int) $templateId,
            'vendor_id' => (int) $vendorId,
            'category_id' => (int) $categoryId,
            'credential_id' => (int) $credentialId,
            'default_group_id' => $defaultGroupId ? (int) $defaultGroupId : null,
            'prompts' => $prompts,
            'tags' => $selectedTags,
            'device_type' => $rConfigType,
            'site_zone_tag_mapping' => $siteZoneMapping,
        ];

        $this->saveMappings($freshMappings);

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

    protected function deleteMapping($deviceType)
    {
        $freshMappings = $this->loadMappings();

        if (! isset($freshMappings[$deviceType])) {
            error("No mapping found for device type '$deviceType'");

            return;
        }

        if (confirm("Are you sure you want to delete the mapping for '$deviceType'?")) {
            unset($freshMappings[$deviceType]);
            $this->saveMappings($freshMappings);

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

    protected function showInfo()
    {
        $this->line('');
        info('===== rConfig NetMRI Device Mappings =====');
        $this->line('');
        $this->line('This command manages device type mappings for importing devices from NetMRI.');
        $this->line('');

        info('Purpose:');
        $this->line('The mappings define how NetMRI device types are translated to rConfig settings,');
        $this->line('including template, vendor, category, credentials, prompts, and tags.');
        $this->line('');

        info('Mappings File:');
        $this->line('Mappings are stored in: ' . $this->mappingsFile);
        $this->line('');

        info('Usage:');
        $this->line('  php artisan rconfig:netmri-device-mappings              Interactive menu mode');
        $this->line('  php artisan rconfig:netmri-device-mappings --list       List all mappings');
        $this->line('  php artisan rconfig:netmri-device-mappings --add        Add a new mapping');
        $this->line('  php artisan rconfig:netmri-device-mappings --edit=TYPE  Edit a mapping');
        $this->line('  php artisan rconfig:netmri-device-mappings --delete=TYPE Delete a mapping');
        $this->line('');

        info('Workflow:');
        $this->line('1. Setup NetMRI connection:  php artisan rconfig:netmri-connection --setup');
        $this->line('2. Create device mappings:    php artisan rconfig:netmri-device-mappings --add');
        $this->line('3. Load devices from NetMRI:  php artisan rconfig:netmri-load-devices');
        $this->line('4. Import to rConfig:         php artisan rconfig:netmri-import-devices');
        $this->line('');

        info('Common NetMRI Device Types:');
        $this->line('- cisco-ios      : Cisco IOS devices');
        $this->line('- cisco-nxos     : Cisco Nexus devices');
        $this->line('- juniper-junos  : Juniper JunOS devices');
        $this->line('- arista-eos     : Arista EOS devices');
        $this->line('- hp-procurve    : HP ProCurve switches');
        $this->line('- paloalto-panos : Palo Alto firewalls');
        $this->line('- fortinet-fortigate : Fortinet firewalls');
        $this->line('');

        note('RELATED COMMANDS:');
        $this->line('rconfig:netmri-connection       - Manage NetMRI API connection');
        $this->line('rconfig:netmri-device-mappings  - Manage device type mappings');
        $this->line('rconfig:netmri-load-devices     - Load devices from NetMRI');
        $this->line('rconfig:netmri-import-devices   - Import devices to rConfig');
    }
}
