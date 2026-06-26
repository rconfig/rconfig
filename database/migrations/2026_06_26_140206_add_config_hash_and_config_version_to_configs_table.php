<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->longText('config_hash')->nullable()->after('config_filesize');
            $table->integer('config_version')->nullable()->after('config_hash');
        });

        // Existing configs have no version chain yet; start them at 0.
        DB::table('configs')->update(['config_version' => 0]);

        Schema::table('configs', function (Blueprint $table) {
            $table->index(['device_id', 'command', 'created_at'], 'idx_configs_device_command_date');
            $table->index(['device_id', 'latest_version'], 'idx_configs_device_latest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropIndex('idx_configs_device_command_date');
            $table->dropIndex('idx_configs_device_latest');
            $table->dropColumn(['config_hash', 'config_version']);
        });
    }
};
