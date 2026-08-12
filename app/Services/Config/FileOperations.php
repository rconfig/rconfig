<?php

namespace App\Services\Config;

use App\Models\Command;
use DateTime;

class FileOperations
{
    public $type;
    public $_command;
    public $_commandObj;
    public $_commandFileName;
    public $_deviceName;
    public $_deviceid;
    public $date;
    public $year;
    public $month;
    public $day;
    public $catfolder;
    public $hostfolder;
    public $yearfolder;
    public $monthfolder;
    public $todayfolder;

    /**
     * Class Constructor
     *
     * @param  string  $date  Full date in 'Ymd' format
     * @param  string  $year  Year in 'Y' format
     * @param  string  $year  month in 'm' format
     * @param  string  $day  month in 'd' format
     * @param  string  $catfolder  Device category name folder
     * @param  string  $hostfolder  Device name folder
     * @param  string  $yearfolder  Year folder
     * @param  string  $monthfolder  Month folder
     * @param  string  $todayfolder  Todays Date folder
     * @return object file object
     */
    public function __construct($command, $catName, $deviceName, $deviceId, $data_basedir, $type)
    {
        // Set some variables for file and folder creation
        $this->type = $type;
        $this->_command = $command;
        $this->_commandFileName = $command;
        $this->_deviceName = $deviceName;
        $this->_deviceid = $deviceId;
        $date = new DateTime;
        $this->date = $date->format('Ymd');
        $this->year = $date->format('Y');
        $this->month = $date->format('M');
        $this->day = $date->format('d');
        $this->catfolder = "{$data_basedir}{$catName}/";
        $this->hostfolder = "{$this->catfolder}{$deviceName}";
        $this->yearfolder = "{$this->hostfolder}/{$this->year}";
        $this->monthfolder = "{$this->yearfolder}/{$this->month}";
        $this->todayfolder = "{$this->monthfolder}/{$this->day}";
    }

    public function saveFile($showCmdOutput)
    {
        $this->_commandObj = Command::where('command', $this->_command)->first();
        $this->checkIfCommandHasAlternateName();
        $fullpath = $this->createFile($this->_commandFileName);
        $filecontents = $this->_eolOperation($showCmdOutput);
        $this->_insertFileContents($filecontents, $fullpath);

        $downloadStatus = true;

        if (! file_exists($fullpath) || filesize($fullpath) == 0) {
            $logmsg = $this->_deviceName . ' - Could not save the file for command: ' . $this->_command . '. Or the configuration was blank.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->_deviceName, $this->_deviceid, 'device');
            $downloadStatus = false;
        }
        $fileSize = filesize($fullpath);

        return ['filepath' => $fullpath, 'filename' => basename($fullpath), 'download_status' => $downloadStatus, 'filesize' => $fileSize];
    }

    public function createFile($command, $isJson = 0)
    {
        $command = $this->cleanDeviceName($command);
        $filename = $isJson ? $this->createJsonFileName($command) : $this->createFileName($command);
        $fullpath = "{$this->todayfolder}/{$filename}";

        // Ensure the directories exist and apply ownership
        $this->ensureDirectoryExists($this->catfolder);
        $this->ensureDirectoryExists($this->hostfolder);
        $this->ensureDirectoryExists($this->yearfolder);
        $this->ensureDirectoryExists($this->monthfolder);
        $this->ensureDirectoryExists($this->todayfolder);

        // Create the file if it doesn't exist and set permissions
        if (! file_exists($fullpath)) {
            $fullpath = str_replace(' ', '_', $fullpath); // Replace spaces in path
            exec('touch ' . escapeshellarg($fullpath)); // Escape the filename for security
            @chmod($fullpath, $this->writableFileMode());
        }

        return (string) $fullpath;
    }

    private function ensureDirectoryExists($directory)
    {
        if (! is_dir($directory)) {
            $mode = $this->directoryMode();
            mkdir($directory, $mode, true); // Ensure all parent directories are created
            // mkdir()'s mode is masked by the process umask, so set it explicitly.
            // Without this the tree can land world traversable on a default umask.
            @chmod($directory, $mode);
            custom_chown($directory);
        }
    }

    private function _insertFileContents($lines, $fullpath)
    {
        // Configs are left read only, so open a write window first. Errors are
        // suppressed in case the ops is not permitted on this filesystem.
        @chmod($fullpath, $this->writableFileMode());
        file_put_contents($fullpath, $lines);
        // Back to read only, with no access for "other". Device configs contain
        // secrets and must not be readable by unprivileged local accounts.
        @chmod($fullpath, $this->fileMode());
    }

    /**
     * Final mode for a stored config file. Read only, no "other" bits.
     */
    private function fileMode(): int
    {
        return (int) config('rConfig.config_file_mode');
    }

    /**
     * Mode for the directories holding stored configs. A tight file mode is
     * defeated by a traversable parent, so these are locked down to match.
     */
    private function directoryMode(): int
    {
        return (int) config('rConfig.config_dir_mode');
    }

    /**
     * The file mode with write added wherever read is already granted, used for
     * the brief window while contents are written. 0440 becomes 0660, so an
     * operator who tightens the file mode does not have it widened here.
     */
    private function writableFileMode(): int
    {
        $mode = $this->fileMode();

        return $mode | (($mode & 0444) >> 1);
    }

    private function createFileName($command)
    {
        $timestamp = date('Gi'); // format 1301
        // Create file name and return it
        $filename = str_replace(' ', '', $command) . '_' . $timestamp . '.txt';
        $filename = str_replace('/', '', $command) . '_' . $timestamp . '.txt';

        return $filename;
    }

    private function createJsonFileName($command)
    {
        $timestamp = date('Gi'); // format 1301
        // Create file name and return it
        $filename = str_replace(' ', '', $command) . '_' . $timestamp . '.json';
        $filename = str_replace('/', '', $command) . '_' . $timestamp . '.json';

        return $filename;
    }

    // cleans deviceNames and commands from .'s and other special characters. used in textFile.class and devices.crud
    public function cleanDeviceName($string)
    {
        $string = str_replace('.', '_', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    // create new array with PHPs EOL parameter
    private function _eolOperation($showCmd)
    {
        if (is_array($showCmd)) {
            return implode(PHP_EOL, $showCmd);
        } else {
            // Handle the case where $input is not an array
            return $showCmd;
        }
    }

    private function checkIfCommandHasAlternateName()
    {
        if ($this->_commandObj && $this->_commandObj->alternate_filename) {
            $this->_commandFileName = $this->_commandObj->alternate_filename;
        }
    }
}
