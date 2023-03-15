<?php

namespace Database\Seeders\demodata;

use App\Models\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoCommandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mikExportId = Command::create(
            [
                'command' => 'export configuration',
                'description' => 'export for mikrotiks',
            ],
        );
        $mikIntId = Command::create(
            [
                'command' => 'interface print',
                'description' => 'interface print for mikrotiks',
            ]
        );

        Command::create(
            [
                "id" => 6001,
                "command" => "Show ip route",
                "description" => "Show ip route",
                "created_at" => "2023-03-11 08:45:53",
                "updated_at" => "2023-03-11 08:45:53",
            ]
        );

        Command::where('command', 'show run')->update(['description' => 'A demo command for multiple categories']);
        Command::where('command', 'show version')->update(['description' => 'A demo command for multiple categories']);
        Command::where('command', 'show clock')->update(['description' => 'A demo command for multiple categories']);
        Command::where('command', 'show ip route')->update(['description' => 'A demo command for multiple categories']);

        DB::table('category_command')->insert(
            [
                'command_id' => 1,
                'category_id' => 2,
            ],
        );
        DB::table('category_command')->insert(
            [
                'command_id' => 2,
                'category_id' => 2,
            ],
        );
        DB::table('category_command')->insert(
            [
                'command_id' => 3,
                'category_id' => 2,
            ]
        );

        DB::table('category_command')->insert(
            [
                'command_id' => $mikExportId->id,
                'category_id' => 60,
            ]
        );
        DB::table('category_command')->insert(
            [
                'command_id' => $mikIntId->id,
                'category_id' => 60,
            ]
        );
    }
}
