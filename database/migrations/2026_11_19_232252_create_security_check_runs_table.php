<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The latest rconfig:security-check run with its full JSON report. Recording a run replaces the previous one.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('security_check_runs')) {
            return;
        }

        Schema::create('security_check_runs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ran_at')->index();
            $table->string('trigger', 20);
            $table->unsignedInteger('user_id')->nullable();
            $table->string('app_version', 50)->nullable();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedSmallInteger('pass_count')->default(0);
            $table->unsignedSmallInteger('warn_count')->default(0);
            $table->unsignedSmallInteger('fail_count')->default(0);
            $table->unsignedSmallInteger('skip_count')->default(0);
            $table->json('results');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_check_runs');
    }
};
