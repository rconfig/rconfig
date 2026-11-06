<?php

namespace App\Enums;

/**
 * Outcome of one rconfig:check-worker-user check. Only Fail makes the command
 * exit non-zero. Warn and Skipped still show on the dashboard and in the output.
 */
enum WorkerCheckStatus: string
{
    case Ok = 'ok';
    case Warn = 'warn';
    case Fail = 'fail';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'OK',
            self::Warn => 'WARN',
            self::Fail => 'FAIL',
            self::Skipped => 'SKIP',
        };
    }
}
