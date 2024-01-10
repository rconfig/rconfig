<?php

namespace Tests\Fasttests\Console\Commands;

use Artisan;
use Tests\TestCase;

class rconfigSearchConfigsTest extends TestCase
{
    protected $user;

    protected $output;

    /** @test */
    public function it_has_rconfigSearchConfigs_command()
    {
        $this->assertTrue(class_exists(\App\Console\Commands\rconfigSearchConfigs::class));
    }

    /** @test */
    public function command_has_required_help_information()
    {
        Artisan::call('rconfig:search-configs -h');
        $result = Artisan::output();
        $arr = explode("\n", $result);
        $this->assertStringContainsString($arr[7], '  Category              The Category of devices to search.');
        $this->assertStringContainsString($arr[8], '  SearchString          The search string. If searching multiple words, wrap in double-quotes.');
    }

    /** @test */
    public function command_squawks_on_missing_args()
    {
        try {
            Artisan::call('rconfig:search-configs');
            $result = Artisan::output();
            $arr = explode("\n", $result);
            $this->fail('Expected Exception has not been raised.');
        } catch (\Exception $ex) {
            $this->assertEquals($ex->getMessage(), 'Not enough arguments (missing: "Category, SearchString").');
        }
    }

    /** @test */
    public function command_squawks_on_too_many_args()
    {
        try {
            Artisan::call('rconfig:search-configs routers sh ip address');
            $result = Artisan::output();
            $arr = explode("\n", $result);
            $this->fail('Expected Exception has not been raised.');
        } catch (\Exception $ex) {
            $this->assertEquals($ex->getMessage(), 'Too many arguments to "rconfig:search-configs" command, expected arguments "Category" "SearchString".');
        }
    }

    /** @test */
    public function command_squawks_on_invalid_category()
    {
        Artisan::call('rconfig:search-configs ascasc string');
        $arr = explode("\n", Artisan::output());
        $this->assertStringContainsString('ascasc" is an invalid category!', $arr[0]);
    }

    /** @test */
    public function command_squawks_on_invalid_lines_type()
    {
        Artisan::call('rconfig:search-configs Routers string --lines=xxx');
        $arr = explode("\n", Artisan::output());
        $this->assertStringContainsString('Invalid lines input', ($arr[0]));
    }

    /** @test */
    public function search_files_no_result_returned()
    {
        Artisan::call('rconfig:search-configs Routers string --lines=0');
        $arr = explode("\n", Artisan::output());
        $this->assertStringContainsString('No results returned. Refine your search parameters!', $arr[0]);
        $this->assertCount(2, $arr);
    }

    /** @test */
    public function search_files_result_returned()
    {
        Artisan::call('rconfig:search-configs Routers snmp --lines=0');
        $arr = explode("\n", Artisan::output());
        // dd($arr);
        $this->assertContains('566  snmp-server host 1.1.1.1 TESTCOMMUNITY', $arr);
        $this->assertGreaterThan(2, $arr);
    }
}
