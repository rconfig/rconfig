
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('fileName', 191)->nullable();
            $table->string('templateName', 191)->nullable();
            $table->string('description', 191)->nullable();
            $table->timestamps();
        });

        DB::table('templates')->insert([
            0 => [
                'id' => '1',
                'fileName' => '/app/rconfig/templates/ios-telnet-noenable.yml',
                'templateName' => 'Cisco IOS - TELNET - No Enable',
                'description' => 'Cisco IOS TELNET based connection without enable mode',
                'created_at' => '2018-02-27 12:09:44',
                'updated_at' => null,
            ],
            1 => [
                'id' => '2',
                'fileName' => '/app/rconfig/templates/ios-telnet-enable.yml',
                'templateName' => 'Cisco IOS - TELNET - Enable',
                'description' => 'Cisco IOS TELNET based connection with enable mode',
                'created_at' => '2018-02-27 12:09:44',
                'updated_at' => null,
            ],
            2 => [
                'id' => '3',
                'fileName' => '/app/rconfig/templates/ios-ssh-noenable.yml',
                'templateName' => 'Cisco IOS - SSH - No Enable',
                'description' => 'Cisco IOS SSH based connection without enable mode',
                'created_at' => '2018-02-27 12:09:44',
                'updated_at' => null,
            ],
            3 => [
                'id' => '4',
                'fileName' => '/app/rconfig/templates/ios-ssh-enable.yml',
                'templateName' => 'Cisco IOS - SSH - Enable',
                'description' => 'Cisco IOS SSH based connection with enable mode',
                'created_at' => '2018-02-27 12:09:44',
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
        Schema::drop('templates');
    }
}
