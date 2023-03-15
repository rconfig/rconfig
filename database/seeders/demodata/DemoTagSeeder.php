<?php

namespace Database\Seeders\demodata;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class DemoTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tag::create(
            [
                'tagname' => 'Firewalls',
                'tagDescription' => 'A demo tag for Firewalls',
            ],
            [
                'tagname' => 'Load Balancers',
                'tagDescription' => 'A demo tag for Load Balancers',
            ],
            [
                'tagname' => 'Wireless',
                'tagDescription' => 'A demo tag for Wireless Devices',
            ]
        );
        Tag::factory()
            ->count(5)
            ->create();
    }
}
