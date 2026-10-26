<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RCO-1836: seed notifications_defaults rows for the health check failed / recovered types.
 *
 * Users without an explicit preference only receive a notification when a default row
 * exists for its type, and NotificationDefaultsSeeder only runs for test data. Values are
 * written literally (not read from the enum) so this migration stays stable if the enum
 * changes later. Existing rows are left alone so an admin's defaults are never overwritten.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const HEALTH_CHECK_DEFAULTS = [
        'system.health_check_failed' => 'Alerts when a system health check (database, Redis, Horizon, disk space, etc.) starts failing',
        'system.health_check_recovered' => 'Notifications when a failing system health check passes again',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications_defaults')) {
            return;
        }

        foreach (self::HEALTH_CHECK_DEFAULTS as $type => $description) {
            if (DB::table('notifications_defaults')->where('notification_type', $type)->exists()) {
                continue;
            }

            DB::table('notifications_defaults')->insert([
                'notification_type' => $type,
                'category' => 'system',
                'default_db' => 1,
                'default_mail' => 1,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('notifications_defaults')) {
            return;
        }

        DB::table('notifications_defaults')
            ->whereIn('notification_type', array_keys(self::HEALTH_CHECK_DEFAULTS))
            ->delete();
    }
};
