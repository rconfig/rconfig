<?php

namespace App\Services\ConfigCompare;

class ConfigCompareExclusionPolicyParser
{
    /**
     * Parse the exclusion policy text into blocks of command + rules.
     *
     * @return array<int, array{description?: string, command?: string, rules: array<int, string>}>
     */
    public function parse(?string $exclusionString): array
    {
        $lines = explode(PHP_EOL, (string) $exclusionString);

        $result = [];
        $currentBlock = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, '//') === 0) {
                // New block starts
                if ($currentBlock !== null) {
                    $result[] = $currentBlock;
                }
                $currentBlock = [
                    'description' => substr($line, 3),
                    'rules' => [],
                ];
            } elseif (strpos($line, '#[') === 0) {
                // Global or command-specific keyword
                if ($currentBlock !== null) {
                    $currentBlock['command'] = trim($line, '#[]');
                }
            } elseif (! empty($line) && $currentBlock !== null) {
                // Rule line
                $currentBlock['rules'][] = $line;
            }
        }

        if ($currentBlock !== null) {
            $result[] = $currentBlock;
        }

        return $result;
    }
}
