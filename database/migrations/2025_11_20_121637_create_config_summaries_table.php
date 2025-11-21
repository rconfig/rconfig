<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('device_id');
            $table->integer('download_status_0_count')->default(0); // 0 = bad
            $table->integer('download_status_1_count')->default(0); // 1 = good
            $table->integer('download_status_2_count')->default(0); // 2 = unknown
            $table->integer('total_count')->default(0);
            $table->integer('total_file_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_summaries');
    }
};
