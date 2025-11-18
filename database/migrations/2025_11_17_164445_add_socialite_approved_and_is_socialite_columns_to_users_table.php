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
        if (Schema::hasColumn('users', 'is_socialite_approved')) {
            return;
        }
        if (Schema::hasColumn('users', 'is_socialite')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_socialite_approved')->default(false);
            $table->boolean('is_socialite')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_socialite_approved')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_socialite_approved');
            });
        }
        if (Schema::hasColumn('users', 'is_socialite')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_socialite');
            });
        }
    }
};
