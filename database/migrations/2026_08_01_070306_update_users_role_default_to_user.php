<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flip the users.role column default from 'Admin' to the lowest privilege role.
 *
 * The previous default meant any insert that omitted `role` produced a full
 * Administrator. That is a fail open default: it turns an unrelated bug into a
 * privilege escalation. Defaulting to 'User' makes the same mistake fail safe.
 *
 * Existing rows are deliberately left untouched so current administrators keep
 * their access. Only the column default changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)->nullable(false)->default('User')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 255)->nullable(false)->default('Admin')->change();
        });
    }
};
