<?php

namespace App\Enums;

/**
 * Outcome of one rconfig:security-check check. Fail exits 2, Warn exits 1.
 */
enum SecurityCheckStatus: string
{
    case Pass = 'pass';
    case Warn = 'warn';
    case Fail = 'fail';
    case Skip = 'skip';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Higher is worse, for sorting and for the overall run status.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Fail => 3,
            self::Warn => 2,
            self::Skip => 1,
            self::Pass => 0,
        };
    }

    public function needsAction(): bool
    {
        return $this === self::Fail || $this === self::Warn;
    }

    public static function fromWorkerStatus(WorkerCheckStatus $status): self
    {
        return match ($status) {
            WorkerCheckStatus::Ok => self::Pass,
            WorkerCheckStatus::Warn => self::Warn,
            WorkerCheckStatus::Fail => self::Fail,
            WorkerCheckStatus::Skipped => self::Skip,
        };
    }
}
