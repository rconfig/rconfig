<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('task_name')->nullable();
            $table->string('task_desc')->nullable();
            $table->string('task_command')->nullable();
            $table->integer('task_categories')->nullable();
            $table->integer('task_devices')->nullable();
            $table->integer('task_tags')->nullable();
            $table->integer('task_snippet')->nullable();
            $table->string('task_cron')->nullable();
            $table->integer('task_email_notify')->nullable();
            $table->integer('download_report_notify')->nullable();
            $table->integer('verbose_download_report_notify')->nullable();
            $table->integer('is_system')->nullable()->default(0);
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
        Schema::drop('tasks');
    }
}
