<?php

beforeEach(function () {
    $this->timezones = require app_path('Http/Controllers/Api/timezone_list.php');
});

test('timezone list file exists', function () {
    expect(app_path('Http/Controllers/Api/timezone_list.php'))->toBeFile();
});

test('timezone list is array', function () {
    expect($this->timezones)->toBeArray();
    expect($this->timezones)->not->toBeEmpty();
});

test('all timezone identifiers are valid iana timezones', function () {
    $validTimezones = DateTimeZone::listIdentifiers();
    $invalidTimezones = [];

    foreach (array_keys($this->timezones) as $timezone) {
        if (! in_array($timezone, $validTimezones)) {
            $invalidTimezones[] = $timezone;
        }
    }

    expect($invalidTimezones)->toBeEmpty('The following timezone identifiers are not valid IANA timezones: ' . implode(', ', $invalidTimezones));
});

test('no deprecated us timezone identifiers', function () {
    $deprecatedPatterns = ['US/', 'Canada/', 'Etc/'];
    $foundDeprecated = [];

    foreach (array_keys($this->timezones) as $timezone) {
        foreach ($deprecatedPatterns as $pattern) {
            if (str_starts_with($timezone, $pattern) && $pattern !== 'Etc/') {
                $foundDeprecated[] = $timezone;
            }
        }
    }

    expect($foundDeprecated)->toBeEmpty('The following deprecated timezone identifiers should be replaced with canonical IANA identifiers: ' . implode(', ', $foundDeprecated));
});

test('timezone identifiers can be instantiated', function () {
    $failedTimezones = [];

    foreach (array_keys($this->timezones) as $timezone) {
        try {
            new DateTimeZone($timezone);
        } catch (Exception $e) {
            $failedTimezones[$timezone] = $e->getMessage();
        }
    }

    expect($failedTimezones)->toBeEmpty('The following timezones could not be instantiated: ' . json_encode($failedTimezones, JSON_PRETTY_PRINT));
});

test('timezone labels are not empty', function () {
    $emptyLabels = [];

    foreach ($this->timezones as $identifier => $label) {
        if (empty(trim($label))) {
            $emptyLabels[] = $identifier;
        }
    }

    expect($emptyLabels)->toBeEmpty('The following timezone identifiers have empty labels: ' . implode(', ', $emptyLabels));
});

test('no duplicate timezone identifiers', function () {
    $identifiers = array_keys($this->timezones);
    $uniqueIdentifiers = array_unique($identifiers);

    expect($identifiers)->toHaveCount(count($uniqueIdentifiers), 'Duplicate timezone identifiers found');
});

test('essential us timezones are present', function () {
    $essentialTimezones = [
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Anchorage',
        'Pacific/Honolulu',
    ];

    foreach ($essentialTimezones as $timezone) {
        expect($this->timezones)->toHaveKey($timezone, message: "Essential timezone '{$timezone}' is missing from the list");
    }
});

test('timezone count is reasonable', function () {
    $count = count($this->timezones);

    expect($count)->toBeGreaterThanOrEqual(50, 'Timezone list should contain at least 50 timezones');

    expect($count)->toBeLessThanOrEqual(200, 'Timezone list contains an unusually high number of timezones');
});
