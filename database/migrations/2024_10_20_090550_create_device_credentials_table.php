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
        Schema::create('device_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('cred_name');
            $table->text('cred_description')->nullable();
            $table->string('cred_username');
            $table->string('cred_password');
            $table->string('cred_enable_password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_credentials');
    }
};
