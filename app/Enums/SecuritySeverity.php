<?php

namespace App\Enums;

/**
 * How much a failing security check matters.
 */
enum SecuritySeverity: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'info';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Higher is worse, for sorting.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Critical => 4,
            self::High => 3,
            self::Medium => 2,
            self::Low => 1,
            self::Info => 0,
        };
    }
}
