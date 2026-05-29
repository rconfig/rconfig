<?php

namespace Database\Seeders\testdata\Devices;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddTags extends Seeder
{
    public static function run(): void
    {
        DB::table('tags')->insert([
            'id' => 1003,
            'tagname' => 'devtag3',
            'tagDescription' => 'test tag description 3',
        ]);
        DB::table('tags')->insert([
            'id' => 1010,
            'tagname' => 'devtag10',
            'tagDescription' => 'This will always be a dummy tag - i.e. no devices attached',
        ]);
    }
}
