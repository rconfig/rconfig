<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * notifications_defaults rows for the security check failed / recovered types. Literal values; existing rows are kept.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const SECURITY_CHECK_DEFAULTS = [
        'system.security_check_failed' => 'Alerts when a Security Center check starts failing',
        'system.security_check_recovered' => 'Notifications when a failing Security Center check passes again',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications_defaults')) {
            return;
        }

        foreach (self::SECURITY_CHECK_DEFAULTS as $type => $description) {
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
            ->whereIn('notification_type', array_keys(self::SECURITY_CHECK_DEFAULTS))
            ->delete();
    }
};
