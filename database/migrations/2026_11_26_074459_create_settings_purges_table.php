<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data purge settings, one row per purge target. Default rows are inserted here because no seeder
 * runs on install or upgrade. Every target ships disabled, so nothing is deleted until an operator
 * enables it.
 */
return new class extends Migration
{
    /** @var array<string, array{retention_days: int, options: array<string, int|string|bool>}> */
    private const DEFAULT_PURGE_SETTINGS = [
        'configs' => [
            'retention_days' => 1825,
            'options' => [
                'batch_size' => 1000,
                'cleanup_frequency' => 'daily',
                'enabled' => false,
                'min_versions_per_device_command' => 3,
                'non_downloaded_retention_days' => 30,
            ],
        ],
        'activity_log' => [
            'retention_days' => 90,
            'options' => ['batch_size' => 1000, 'cleanup_frequency' => 'daily', 'enabled' => false],
        ],
        'failed_jobs' => [
            'retention_days' => 7,
            'options' => ['batch_size' => 500, 'cleanup_frequency' => 'daily', 'enabled' => false],
        ],
        'job_batches' => [
            'retention_days' => 14,
            'options' => ['batch_size' => 500, 'cleanup_frequency' => 'daily', 'enabled' => false],
        ],
        'jobs' => [
            'retention_days' => 7,
            'options' => ['batch_size' => 2000, 'cleanup_frequency' => 'daily', 'enabled' => false],
        ],
        'tracked_jobs' => [
            'retention_days' => 30,
            'options' => ['batch_size' => 1000, 'cleanup_frequency' => 'daily', 'enabled' => false],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings_purge')) {
            Schema::create('settings_purge', function (Blueprint $table) {
                $table->id();
                $table->string('target')->unique();
                $table->unsignedInteger('retention_days')->default(0);
                $table->json('options')->nullable();
                $table->timestamps();
            });
        }

        foreach (self::DEFAULT_PURGE_SETTINGS as $target => $defaults) {
            if (DB::table('settings_purge')->where('target', $target)->exists()) {
                continue;
            }

            DB::table('settings_purge')->insert([
                'target' => $target,
                'retention_days' => $defaults['retention_days'],
                'options' => json_encode($defaults['options']),
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
        Schema::dropIfExists('settings_purge');
    }
};
