<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vendorName', 191);
            $table->timestamps();
        });

        DB::table('vendors')->insert([
            0 => [
                'vendorName' => 'Aruba',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            1 => [
                'vendorName' => 'Brocade',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            2 => [
                'vendorName' => 'Checkpoint',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            3 => [
                'vendorName' => 'Cisco',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            4 => [
                'vendorName' => 'Dell',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            5 => [
                'vendorName' => 'Extreme',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
            ],
            6 => [
                'vendorName' => 'Fortinet',
                'created_at' => '2018-06-07 13:42:08',
                'updated_at' => '2018-06-07 13:42:08',
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
        Schema::drop('vendors');
    }
}
