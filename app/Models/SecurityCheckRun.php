<?php

namespace App\Models;

use App\Services\Security\SecurityReport;
use Database\Factories\SecurityCheckRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The latest stored rconfig:security-check run. Only one row is kept.
 */
class SecurityCheckRun extends Model
{
    /** @use HasFactory<SecurityCheckRunFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ran_at' => 'datetime',
            'results' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function latestRun(): ?self
    {
        return static::query()->orderByDesc('ran_at')->orderByDesc('id')->first();
    }

    public function report(): SecurityReport
    {
        return SecurityReport::fromArray((array) $this->results);
    }

    /**
     * @return array{id: int, ran_at: string|null, trigger: string, user: string|null, app_version: string|null, duration_ms: int, summary: array{pass: int, warn: int, fail: int, skip: int}}
     */
    public function toSummaryArray(): array
    {
        return [
            'id' => (int) $this->id,
            'ran_at' => $this->ran_at?->toIso8601String(),
            'trigger' => (string) $this->trigger,
            'user' => $this->user?->name,
            'app_version' => $this->app_version,
            'duration_ms' => (int) $this->duration_ms,
            'summary' => [
                'pass' => (int) $this->pass_count,
                'warn' => (int) $this->warn_count,
                'fail' => (int) $this->fail_count,
                'skip' => (int) $this->skip_count,
            ],
        ];
    }
}
