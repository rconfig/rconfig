<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * History of data purge runs, one row per run of rconfig:data-purge.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('purge_runs')) {
            return;
        }

        Schema::create('purge_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('mode', 50)->nullable();
            $table->boolean('dry_run')->default(false);
            $table->string('status', 50)->default('pending')->index();
            $table->string('source', 150)->nullable();
            $table->json('scope')->nullable();
            $table->json('summary')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purge_runs');
    }
};
