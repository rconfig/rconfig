<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description', 65535);
            $table->integer('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->integer('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->text('properties', 65535)->nullable();
            $table->string('event_type')->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_id')->nullable();
            $table->string('events_ids')->nullable();
            $table->string('connection_category')->nullable();
            $table->text('connection_ids', 65535)->nullable();
            $table->text('class', 65535)->nullable();
            $table->text('function', 65535)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('activity_log');
    }
}
