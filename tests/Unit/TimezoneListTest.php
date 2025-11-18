<?php

namespace Tests\Unit;

use Tests\TestCase;

class TimezoneListTest extends TestCase
{
    protected array $timezones;

    public function setUp(): void
    {
        parent::setUp();
        $this->timezones = require app_path('Http/Controllers/Api/timezone_list.php');
    }

    public function test_timezone_list_file_exists()
    {
        $this->assertFileExists(app_path('Http/Controllers/Api/timezone_list.php'));
    }

    public function test_timezone_list_is_array()
    {
        $this->assertIsArray($this->timezones);
        $this->assertNotEmpty($this->timezones);
    }

    public function test_all_timezone_identifiers_are_valid_iana_timezones()
    {
        $validTimezones = \DateTimeZone::listIdentifiers();
        $invalidTimezones = [];

        foreach (array_keys($this->timezones) as $timezone) {
            if (!in_array($timezone, $validTimezones)) {
                $invalidTimezones[] = $timezone;
            }
        }

        $this->assertEmpty(
            $invalidTimezones,
            'The following timezone identifiers are not valid IANA timezones: ' . implode(', ', $invalidTimezones)
        );
    }

    public function test_no_deprecated_us_timezone_identifiers()
    {
        $deprecatedPatterns = ['US/', 'Canada/', 'Etc/'];
        $foundDeprecated = [];

        foreach (array_keys($this->timezones) as $timezone) {
            foreach ($deprecatedPatterns as $pattern) {
                if (str_starts_with($timezone, $pattern) && $pattern !== 'Etc/') {
                    $foundDeprecated[] = $timezone;
                }
            }
        }

        $this->assertEmpty(
            $foundDeprecated,
            'The following deprecated timezone identifiers should be replaced with canonical IANA identifiers: ' . implode(', ', $foundDeprecated)
        );
    }

    public function test_timezone_identifiers_can_be_instantiated()
    {
        $failedTimezones = [];

        foreach (array_keys($this->timezones) as $timezone) {
            try {
                new \DateTimeZone($timezone);
            } catch (\Exception $e) {
                $failedTimezones[$timezone] = $e->getMessage();
            }
        }

        $this->assertEmpty(
            $failedTimezones,
            'The following timezones could not be instantiated: ' . json_encode($failedTimezones, JSON_PRETTY_PRINT)
        );
    }

    public function test_timezone_labels_are_not_empty()
    {
        $emptyLabels = [];

        foreach ($this->timezones as $identifier => $label) {
            if (empty(trim($label))) {
                $emptyLabels[] = $identifier;
            }
        }

        $this->assertEmpty(
            $emptyLabels,
            'The following timezone identifiers have empty labels: ' . implode(', ', $emptyLabels)
        );
    }

    public function test_no_duplicate_timezone_identifiers()
    {
        $identifiers = array_keys($this->timezones);
        $uniqueIdentifiers = array_unique($identifiers);

        $this->assertCount(
            count($uniqueIdentifiers),
            $identifiers,
            'Duplicate timezone identifiers found'
        );
    }

    public function test_essential_us_timezones_are_present()
    {
        $essentialTimezones = [
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'America/Anchorage',
            'Pacific/Honolulu',
        ];

        foreach ($essentialTimezones as $timezone) {
            $this->assertArrayHasKey(
                $timezone,
                $this->timezones,
                "Essential timezone '{$timezone}' is missing from the list"
            );
        }
    }

    public function test_timezone_count_is_reasonable()
    {
        $count = count($this->timezones);

        $this->assertGreaterThanOrEqual(
            50,
            $count,
            'Timezone list should contain at least 50 timezones'
        );

        $this->assertLessThanOrEqual(
            200,
            $count,
            'Timezone list contains an unusually high number of timezones'
        );
    }
}
