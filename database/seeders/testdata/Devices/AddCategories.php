<?php

namespace Database\Seeders\testdata\Devices;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddCategories extends Seeder
{
    public static function run(): void
    {
        DB::table('categories')->insert([
            'id' => 1010,
            'categoryName' => 'devcategory10',
            'categoryDescription' => 'This will always be a dummy category - i.e. no devices attached',
        ]);
        DB::table('categories')->insert([
            'id' => 1011,
            'categoryName' => 'devcategory11',
            'categoryDescription' => 'This will always be a dummy category - i.e. no devices attached, but has a command',
        ]);
        DB::table('categories')->insert([
            'id' => 8,
            'categoryName' => 'Unreachable_devices',
            'categoryDescription' => null,
            'badgeColor' => 'badge-primary',
            'created_at' => '2018-06-06 22:20:44',
            'updated_at' => null,
        ]);
        DB::table('categories')->insert([
            'id' => 6000,
            'categoryName' => 'dev_cat',
        ]);
        DB::table('categories')->insert([
            'id' => 11,
            'categoryName' => 'dev_linux',
        ]);
    }
}
