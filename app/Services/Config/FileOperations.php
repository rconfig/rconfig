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
     * @param  string  $date Full date in 'Ymd' format
     * @param  string  $year Year in 'Y' format
     * @param  string  $year month in 'm' format
     * @param  string  $day month in 'd' format
     * @param  string  $catfolder Device category name folder
     * @param  string  $hostfolder Device name folder
     * @param  string  $yearfolder Year folder
     * @param  string  $monthfolder Month folder
     * @param  string  $todayfolder Todays Date folder
     * @return object  file object
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

        if (!file_exists($fullpath) || filesize($fullpath) == 0) {
            $logmsg = $this->_deviceName . ' - Could not save the file for command: ' . $this->_command . '. Or the configuration was blank.';
            dump($logmsg);
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
            chmod($fullpath, 0666);
        }

        return (string) $fullpath;
    }

    private function ensureDirectoryExists($directory)
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true); // Ensure all parent directories are created
            custom_chown($directory);
        }
    }

    private function _insertFileContents($lines, $fullpath)
    {
        // if the file is alread in place chmod it to 666 before writing info
        @chmod($fullpath, 0666); // disabled errors in case ops is not permitted
        // dump array into file & chmod back to RO
        $filehandle = fopen($fullpath, 'w+');
        file_put_contents($fullpath, $lines);
        fclose($filehandle);
        @chmod($fullpath, 0444); // disabled errors in case ops is not permitted
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
