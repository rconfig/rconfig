<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoryCommandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_command', function (Blueprint $table) {
            $table->integer('category_id')->nullable();
            $table->integer('command_id')->nullable();
        });

        DB::table('category_command')->insert([
            0 => [
                'category_id' => '1',
                'command_id' => '1',
            ],
            1 => [
                'category_id' => '1',
                'command_id' => '2',
            ],
            2 => [
                'category_id' => '1',
                'command_id' => '3',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('category_command');
    }
}
