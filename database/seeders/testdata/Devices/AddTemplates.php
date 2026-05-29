<?php

namespace Database\Seeders\testdata\Devices;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddTemplates extends Seeder
{
    public static function run(): void
    {
        DB::table('templates')->insert([
            'id' => 11,
            'fileName' => '/app/rconfig/templates/ssh_priv_key_test.yml',
            'templateName' => 'ssh_priv_key_test',
            'description' => 'ssh_priv_key_test descr',
            'created_at' => '2021-02-27 12:09:44',
            'updated_at' => null,
        ]);
    }
}
