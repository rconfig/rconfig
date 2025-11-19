<?php

namespace Tests;

use App\Models\Template;
use Tests\Traits\ManagesTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use MigrateFreshSeedOnce, CreatesApplication, ManagesTransactions;

    public function log_message_during_test($testname, $message)
    {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, '----------------------------' . "\n");
        fwrite(STDOUT, $testname . "\n");
        fwrite(STDOUT, $message . "\n");
        fwrite(STDOUT, '----------------------------' . "\n");
    }


    public function add_5_sec_timeout_telnet_noenable_template()
    {
        $this->add_specific_connection_template('ios-telnet-noenable-test-timeout-5.yml', 1);
    }

    public function remove_5_sec_timeout_telnet_noenable_template()
    {
        $this->remove_specific_connection_template('ios-telnet-noenable.yml', 'ios-telnet-noenable-test-timeout-5.yml', 1);
    }

    public function add_5_sec_timeout_ssh_noenable_template()
    {
        $this->add_specific_connection_template('ios-ssh-noenable-test-timeout-5.yml', 3);
    }

    public function remove_5_sec_timeout_ssh_noenable_template()
    {
        $this->remove_specific_connection_template('ios-ssh-noenable.yml', 'ios-ssh-noenable-test-timeout-5.yml', 3);
    }

    private function add_specific_connection_template($filename, $id)
    {
        // copy test template with lower timeout value to storage
        if (file_exists(base_path('tests/storage/' . $filename))) {
            $this->assertTrue(true);
        }
        if (!copy(base_path('tests/storage/' . $filename), storage_path() . '/app/rconfig/templates/' . $filename)) {
            $this->assertTrue(false);
            // echo "failed to copy $file...\n";
        }
        Template::find($id)->update([
            'fileName' => '/app/rconfig/templates/' . $filename,
        ]);
    }

    private function remove_specific_connection_template($oldfilename, $newfilename, $id)
    {
        // remove the test template from storage
        unlink(storage_path() . '/app/rconfig/templates/' . $newfilename);
        if (!file_exists(storage_path() . '/app/rconfig/templates/' . $newfilename)) {
            $this->assertTrue(true);
        }

        // change template filename back to original
        Template::find($id)->update([
            'fileName' => '/app/rconfig/templates/' . $oldfilename,
        ]);
    }
}
