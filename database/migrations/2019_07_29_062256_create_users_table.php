<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('role', 50)->nullable()->comment('0 = user, 1 = admin');
            $table->timestamps();
            $table->text('settings', 65535)->nullable();
        });

        DB::table('users')->insert([
            0 => [
                'id' => '1',
                'name' => 'admin',
                'email' => 'admin@domain.com',
                'email_verified_at' => null,
                'password' => '$2y$10$ZF3AXzM/N/xduWF0CQiswOaHYh6EwiNcWJ8AUp.7xv0qDcfLvVsGi',
                'remember_token' => null,
                'role' => 'Admin',
                'created_at' => '2019-07-15 14:25:26',
                'updated_at' => '2019-07-15 14:25:26',
                'settings' => '{"devices_view":"display1"}',
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
        Schema::dropIfExists('users');
    }
}
