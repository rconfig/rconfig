<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('categoryName', 191)->unique('categoryName');
            $table->text('categoryDescription', 65535)->nullable();
            $table->string('badgeColor', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('updated_at', 50)->nullable();
        });

        DB::table('categories')->insert([
            0 => [
                'id' => '1',
                'categoryName' => 'Routers',
                'categoryDescription' => null,
                'badgeColor' => 'badge-primary',
                'created_at' => '2018-06-06 22:20:44',
                'updated_at' => null,
            ],
            1 => [
                'id' => '2',
                'categoryName' => 'Switches',
                'categoryDescription' => null,
                'badgeColor' => 'bg-danger',
                'created_at' => '2018-06-06 22:20:52',
                'updated_at' => null,
            ],
            2 => [
                'id' => '3',
                'categoryName' => 'Firewalls',
                'categoryDescription' => null,
                'badgeColor' => 'badge-warning',
                'created_at' => '2018-06-06 21:21:04',
                'updated_at' => null,
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
        Schema::drop('categories');
    }
}
