<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('login_banner', 65535)->nullable();
            $table->string('timezone')->nullable();
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail_from_email')->nullable();
            $table->text('mail_to_email', 65535)->nullable();
            $table->integer('mail_authcheck')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password', 512)->nullable();
            $table->string('mail_driver')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('defaultDeviceUsername')->nullable();
            $table->string('defaultDevicePassword')->nullable();
            $table->string('defaultEnablePassword', 512)->nullable();
            $table->integer('passwordEncryption')->nullable()->default(0)->comment('0 - no encryption, 1 = encryption');
            $table->integer('deviceDebugging')->nullable();
            $table->integer('phpDebugging')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'id' => '1',
            'login_banner' => 'Authorization message - You must be an authorized user to login and use this system.',
            'timezone' => 'Europe/Dublin',
            'mail_host' => 'devmailer.rconfig.com',
            'mail_port' => 1025,
            'mail_from_email' => 'admin@domain.com',
            'mail_to_email' => 'user@domain.com',
            'mail_authcheck' => '0',
            'mail_username' => null,
            'mail_password' => null,
            'mail_driver' => 'smtp',
            'mail_encryption' => null,
            'defaultDeviceUsername' => null,
            'defaultDevicePassword' => null,
            'defaultEnablePassword' => null,
            'passwordEncryption' => '1',
            'deviceDebugging' => '0',
            'phpDebugging' => '1',
            'created_at' => '2019-07-15 18:38:03',
            'updated_at' => '2019-07-15 18:38:23',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings');
    }
}
