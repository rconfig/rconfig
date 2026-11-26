<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * notifications_defaults row for the data purge completed notification. Without it nobody receives
 * the notification unless they have set an explicit preference. Literal values; an existing row is kept.
 */
return new class extends Migration
{
    private const NOTIFICATION_TYPE = 'config.purge_completed';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications_defaults')) {
            return;
        }

        if (DB::table('notifications_defaults')->where('notification_type', self::NOTIFICATION_TYPE)->exists()) {
            return;
        }

        DB::table('notifications_defaults')->insert([
            'notification_type' => self::NOTIFICATION_TYPE,
            'category' => 'config',
            'default_db' => 1,
            'default_mail' => 1,
            'description' => 'Alerts when configuration purge operations finish',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations. The row may predate this migration, so it is left in place.
     */
    public function down(): void {}
};
