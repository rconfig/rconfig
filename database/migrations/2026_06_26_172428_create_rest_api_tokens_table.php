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
        Schema::create('rest_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->longText('api_token')->nullable();
            $table->string('api_token_name', 191)->nullable();
            $table->integer('api_token_status')->nullable();
            $table->dateTime('api_token_expire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_api_tokens');
    }
};
