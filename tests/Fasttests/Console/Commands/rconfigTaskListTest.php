<?php

namespace Tests\Fasttests\Console\Commands;

use App\Models\Task;
use Artisan;
use Tests\TestCase;

class rconfigTaskListTest extends TestCase
{
    protected $output;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_has_rconfigTaskList_command()
    {
        $this->assertTrue(class_exists(\App\Console\Commands\rconfigTaskList::class));
    }

    /** @test */
    public function list_tasks_command()
    {
        $tasks = Task::factory(20)->create();

        Artisan::call('rconfig:list-tasks');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Results for Tasks List:');
        $this->assertTrue($this->array_search_partial('555555', $arr));

        $this->assertGreaterThan(20, $arr);
    }

    public function array_search_partial($keyword, $arr)
    {
        foreach ($arr as $index => $string) {
            if (strpos($string, $keyword) !== false) {
                return true;
            }
        }
    }
}
