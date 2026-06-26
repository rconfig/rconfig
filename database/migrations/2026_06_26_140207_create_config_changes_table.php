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
        Schema::create('config_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('current_config_id')->index();
            $table->unsignedBigInteger('previous_config_id')->index();
            $table->integer('config_version');
            $table->string('config_change_type');
            $table->longText('config_diff');
            $table->longText('compare_exclusion_settings')->nullable();
            $table->string('change_trigger')->default('pull');
            $table->timestamps();

            $table->index(['created_at', 'config_change_type'], 'idx_changes_date_type');
            $table->index(['current_config_id', 'created_at'], 'idx_changes_config_date');
        });

        // Full-text index for searching within diffs (MySQL only).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE config_changes ADD FULLTEXT idx_config_diff_fulltext (config_diff)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_changes');
    }
};
