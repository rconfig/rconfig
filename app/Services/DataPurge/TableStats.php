<?php

namespace App\Services\DataPurge;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TableStats
{
    /**
     * Approximate row counts and on-disk size, read from information_schema so it stays fast on large tables.
     *
     * @param  list<string>  $tables
     * @return array<string, array{rows: int, bytes: int}>
     */
    public static function forTables(array $tables, int $ttlSeconds = 30): array
    {
        $tables = array_values(array_unique(array_filter($tables)));

        if (empty($tables)) {
            return [];
        }

        $cacheKey = 'tablestats.' . app()->environment() . '.' . md5(implode('|', $tables)) . '.' . config('database.default');

        return Cache::remember($cacheKey, $ttlSeconds, fn (): array => self::mysqlStats($tables));
    }

    /**
     * @param  list<string>  $tables
     * @return array<string, array{rows: int, bytes: int}>
     */
    private static function mysqlStats(array $tables): array
    {
        $stats = array_fill_keys($tables, ['rows' => 0, 'bytes' => 0]);
        $placeholders = implode(',', array_fill(0, count($tables), '?'));

        $rows = DB::select(
            "SELECT TABLE_NAME AS t,
                    COALESCE(TABLE_ROWS, 0) AS rows_est,
                    COALESCE(DATA_LENGTH, 0) + COALESCE(INDEX_LENGTH, 0) AS total_bytes
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ($placeholders)",
            $tables
        );

        foreach ($rows as $row) {
            $stats[$row->t] = ['rows' => (int) $row->rows_est, 'bytes' => (int) $row->total_bytes];
        }

        return $stats;
    }

    public static function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB', 'PB'];
        $index = min((int) floor(log($bytes, 1024)), count($units)) - 1;

        return number_format($bytes / (1024 ** ($index + 1)), 2) . ' ' . $units[$index];
    }
}
