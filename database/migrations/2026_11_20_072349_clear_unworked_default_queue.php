<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

return new class extends Migration
{
    /**
     * Drops jobs left on the redis "default" queue, which no Horizon supervisor works. Their
     * emails and update checks are stale, so they are cleared, not run (RCO-2244).
     */
    public function up(): void
    {
        if (config('queue.default') !== 'redis') {
            return;
        }

        try {
            $cleared = Queue::connection('redis')->clear('default');
        } catch (Throwable $exception) {
            Log::warning('Could not clear the unworked default queue: ' . $exception->getMessage());

            return;
        }

        if ($cleared > 0) {
            Log::info("Cleared {$cleared} stale job(s) from the unworked default queue.");
        }
    }

    public function down(): void
    {
        // Cleared jobs cannot be restored.
    }
};
