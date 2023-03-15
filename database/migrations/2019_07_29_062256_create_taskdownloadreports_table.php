<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaskdownloadreportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taskdownloadreports', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('report_id', 50)->nullable();
            $table->integer('task_id');
            $table->string('task_name')->nullable();
            $table->string('task_desc')->nullable();
            $table->string('task_type')->default('');
            $table->string('file_name')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('duration')->nullable();

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
        Schema::drop('taskdownloadreports');
    }
}
