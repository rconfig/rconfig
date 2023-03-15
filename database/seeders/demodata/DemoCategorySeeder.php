<?php

namespace Database\Seeders\demodata;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DemoCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create(
            [
                'id' => 60,
                'categoryName' => 'Mikrotiks',
                'categoryDescription' => 'A demo category for Mikrotiks',
            ],
        );
        Category::create(
            [
                'categoryName' => 'VPN-Devices',
                'categoryDescription' => 'A demo category for VPN-Devices',
            ]
        );
        Category::create(
            [
                'categoryName' => 'Wireless',
                'categoryDescription' => 'A demo category for Wireless Devices',
            ]
        );
        Category::where('categoryName', 'Firewalls')->update(['categoryDescription' => 'A demo category for Firewall Devices']);
        Category::where('categoryName', 'Switches')->update(['categoryDescription' => 'A demo category for Switches']);
        Category::where('categoryName', 'Routers')->update(['categoryDescription' => 'A demo category for Routers']);
    }
}
