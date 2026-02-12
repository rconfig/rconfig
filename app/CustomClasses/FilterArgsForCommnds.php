<?php

namespace App\CustomClasses;

class FilterArgsForCommnds
{
    private $args;

    public function __construct()
    {
    }

    public function sanitize($args)
    {
        $this->args = $args;
        // Cast all string digit values to int, keep ints, filter out anything else
        $filtered = array_map(function ($item) {
            if (is_int($item)) {
                return $item;
            }
            if (is_string($item) && ctype_digit($item)) {
                return (int)$item;
            }

            \Log::debug('[DeviceIdSanitizer] Invalid device ID encountered during sanitization', ['item' => $item]);
            return null;
        }, $this->args);
        // Remove nulls and non-integers


        return array_values(array_filter($filtered, function ($item) {
            return is_int($item);
        }));
    }
}
