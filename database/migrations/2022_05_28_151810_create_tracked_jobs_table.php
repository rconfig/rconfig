<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackedJobsTable extends Migration
{
    public function up()
    {
        Schema::create('tracked_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trackable_id')->index();
            $table->string('trackable_type')->index();
            $table->string('queue')->nullable();
            $table->string('status')->default('queued');
            $table->longText('payload')->nullable();
            $table->longText('command')->nullable();
            $table->integer('device_id')->nullable();
            $table->longText('output')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracked_jobs');
    }
}
