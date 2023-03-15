<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('tagname', 50);
            $table->string('tagDescription')->nullable();
            $table->timestamps();
        });

        DB::table('tags')->insert([
            0 => [
                'id' => '1',
                'tagname' => 'Routers',
                'tagDescription' => 'A Tag for Routers',
                'created_at' => '2019-07-16 05:51:44',
                'updated_at' => '2019-07-16 05:51:44',
            ],
            1 => [
                'id' => '2',
                'tagname' => 'Switches',
                'tagDescription' => 'A Tag for Switches',
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
        Schema::drop('tags');
    }
}
