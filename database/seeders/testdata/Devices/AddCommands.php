<?php

namespace Database\Seeders\testdata\Devices;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddCommands extends Seeder
{
    public static function run(): void
    {
        DB::table('commands')->insert([
            'id' => 6000,
            'command' => 'dev_cmd',
        ]);
        DB::table('commands')->insert([
            'id' => 11,
            'command' => 'hostname',
        ]);

        DB::table('category_command')->insert([
            'category_id' => 6000,
            'command_id' => 6000,
        ]);
        DB::table('category_command')->insert([
            'category_id' => 8,
            'command_id' => 1,
        ]);
        DB::table('category_command')->insert([
            'category_id' => 11,
            'command_id' => 11,
        ]);
        DB::table('category_command')->insert([
            'category_id' => 1011,
            'command_id' => 6000,
        ]);
    }
}
