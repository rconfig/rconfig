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
        Schema::create('notifications_user_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('notification_type');
            $table->string('channel', 20);
            $table->tinyInteger('enabled')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'notification_type', 'channel'], 'user_notification_unique');
            $table->index(['user_id', 'enabled']);
            $table->index(['notification_type', 'channel', 'enabled'], 'notif_pref_type_channel_enabled_idx');
        });

        DB::statement("ALTER TABLE notifications_user_preferences ADD CONSTRAINT check_channel CHECK (channel IN ('db', 'mail'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_user_preferences');
    }
};
