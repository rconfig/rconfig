<?php

namespace Tests\Fasttests\Console\Commands;

use App\Console\Commands\rconfigCatList;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class rconfigCategoryListTest extends TestCase
{
    protected $user;
    protected $output;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_has_rconfig_category_list_command()
    {
        $this->assertTrue(class_exists(rconfigCatList::class));
    }

    public function test_list_category_command()
    {
        Artisan::call('rconfig:list-categories');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Results for Categories List:');
        $this->assertStringContainsString('Routers', $arr[4]);
        $this->assertStringContainsString('Switches', $arr[5]);
    }
}
