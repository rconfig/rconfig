<?php

namespace Tests\Fasttests\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class rconfigCategoryListTest extends TestCase
{
    protected $user;

    protected $output;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_has_rconfigCategoryList_command()
    {
        $this->assertTrue(class_exists(\App\Console\Commands\rconfigCatList::class));
    }

    /** @test */
    public function list_category_command()
    {
        Artisan::call('rconfig:list-categories');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Results for Categories List:');
        $this->assertStringContainsString('Routers', $arr[4]);
        $this->assertStringContainsString('Switches', $arr[5]);
    }
}
