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
        Schema::create('notifications_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type')->unique();
            $table->string('category');
            $table->tinyInteger('default_db')->default(1)->comment('Boolean: 0=false, 1=true');
            $table->tinyInteger('default_mail')->default(0)->comment('Boolean: 0=false, 1=true');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_defaults');
    }
};
