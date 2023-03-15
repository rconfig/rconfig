<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('device_name', 191)->nullable();
            $table->string('device_ip', 191)->nullable();
            $table->integer('device_default_creds_on')->nullable();
            $table->string('device_username', 191)->nullable();
            $table->string('device_password')->nullable();
            $table->string('device_enable_password')->nullable();
            $table->string('device_main_prompt')->nullable();
            $table->string('device_enable_prompt', 191)->nullable();
            $table->integer('device_category_id')->nullable();
            $table->integer('device_template')->nullable();
            $table->string('device_model', 191)->nullable();
            $table->string('device_version', 191)->nullable();
            $table->string('device_added_by', 191)->nullable()->default('-');
            $table->timestamps();
            $table->integer('status')->nullable()->default(1);
            $table->timestamp('last_seen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('devices');
    }
}
