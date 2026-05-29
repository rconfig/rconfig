<?php

namespace Tests\Fasttests\Console\Commands;

use App\Console\Commands\rconfigTagList;
use Artisan;
use Tests\TestCase;

class rconfigTagListTest extends TestCase
{
    protected $user;
    protected $output;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_has_rconfig_tag_list_command()
    {
        $this->assertTrue(class_exists(rconfigTagList::class));
    }

    public function test_list_tags_command()
    {
        Artisan::call('rconfig:list-tags');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Results for Tags List:');
        $this->assertStringContainsString('Routers', $arr[4]);
        $this->assertStringContainsString('Switches', $arr[5]);
    }
}
