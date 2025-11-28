<?php

use Database\Seeders\IntegrationOptionSeeder;
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
        Schema::create('integration_options', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('name');
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('action_text')->nullable();
            $table->string('config_url')->nullable();
            $table->boolean('external_url')->default(false);
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        $seeder = new IntegrationOptionSeeder;
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_options');
    }
};
