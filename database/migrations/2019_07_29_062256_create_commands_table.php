<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('command', 255);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('commands')->insert([
            0 => [
                'id' => '1',
                'command' => 'show clock',
                'description' => 'An Example command',
                'created_at' => '2019-07-16 05:51:44',
                'updated_at' => '2019-07-16 05:51:44',
            ],
            1 => [
                'id' => '2',
                'command' => 'show version',
                'description' => 'An Example command',
                'created_at' => '2019-07-16 05:51:44',
                'updated_at' => '2019-07-16 05:51:44',
            ],
            2 => [
                'id' => '3',
                'command' => 'show run',
                'description' => 'An Example command',
                'created_at' => '2019-07-16 05:51:44',
                'updated_at' => '2019-07-16 05:51:44',
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
        Schema::drop('commands');
    }
}
