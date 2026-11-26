<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingsPurge extends BaseModel
{
    use HasFactory;

    /** The purge target handled by the config retention rules rather than a plain table delete. */
    public const TARGET_CONFIGS = 'configs';

    /**
     * Tables the generic age-based purge may delete from. A row for any other target is refused.
     *
     * @var list<string>
     */
    public const GENERIC_TARGETS = ['activity_log', 'failed_jobs', 'job_batches', 'jobs', 'tracked_jobs'];

    /** @var list<string> */
    public const CLEANUP_FREQUENCY_OPTIONS = ['daily', 'weekly', 'monthly'];

    /** 1 when options.enabled is true, otherwise 0. JSON_CONTAINS avoids a collation clash on the JSON text. */
    public const ENABLED_SQL = "COALESCE(JSON_CONTAINS(options, 'true', '$.enabled'), 0)";

    protected $table = 'settings_purge';
    protected $fillable = [
        'target',
        'retention_days',
        'options',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'retention_days' => 'integer',
            'options' => 'array',
        ];
    }

    /**
     * A target is only purged when it has been explicitly enabled.
     */
    public function isEnabled(): bool
    {
        return filter_var($this->options['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function getBatchSize(): int
    {
        return max(1, (int) ($this->options['batch_size'] ?? 1000));
    }

    public function getCleanupFrequency(): string
    {
        return $this->options['cleanup_frequency'] ?? 'daily';
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->whereRaw(self::ENABLED_SQL . ' = 1');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where('target', 'LIKE', "%{$search}%");
    }
}
