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
        Schema::table('commands', function (Blueprint $table) {
            $table->boolean('base64')->default(false);
        });
        Schema::table('configs', function (Blueprint $table) {
            $table->boolean('base64')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commands', function (Blueprint $table) {
            $table->dropColumn('base64');
        });
        Schema::table('configs', function (Blueprint $table) {
            $table->dropColumn('base64');
        });
    }
};
