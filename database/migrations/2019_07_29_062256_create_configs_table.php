<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_category')->nullable();
            $table->string('command')->nullable();
            $table->string('type')->nullable();
            $table->integer('download_status')->nullable();
            $table->string('report_id')->nullable();
            $table->string('config_location')->nullable();
            $table->string('config_filename')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('configs');
    }
}
