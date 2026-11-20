<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security Center alert toggle. Null falls back to config('security-checks.notify').
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('settings', 'security_check_notify')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('security_check_notify')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('settings', 'security_check_notify')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('security_check_notify');
        });
    }
};
