<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the `attribute_changes` column introduced by spatie/laravel-activitylog v5,
     * which the Activity model now writes on every logged activity.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->json('attribute_changes')->nullable()->after('properties');
        });

        Schema::table('activity_log_archives', function (Blueprint $table) {
            $table->json('attribute_changes')->nullable()->after('properties');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('attribute_changes');
        });

        Schema::table('activity_log_archives', function (Blueprint $table) {
            $table->dropColumn('attribute_changes');
        });
    }
};
