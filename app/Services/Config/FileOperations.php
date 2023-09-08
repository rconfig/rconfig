<?php

namespace App\Services\Config;

class FileOperations
{
    public $type;
    public $_command;
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
    public function __construct($command, $catName, $deviceName, $deviceId, $data_basedir)
    {
        // Set some variables for file and folder creation
        $this->_command = $command;
        $this->_deviceName = $deviceName;
        $this->_deviceid = $deviceId;
        $this->date = date('Ymd');
        $this->year = date('Y');
        $this->month = date('M');
        $this->day = date('d');
        $this->catfolder = $data_basedir . $catName . '/';
        $this->hostfolder = $data_basedir . $catName . '/' . $deviceName;
        $this->yearfolder = $data_basedir . $catName . '/' . $deviceName . '/' . $this->year;
        $this->monthfolder = $data_basedir . $catName . '/' . $deviceName . '/' . $this->year . '/' . $this->month;
        $this->todayfolder = $data_basedir . $catName . '/' . $deviceName . '/' . $this->year . '/' . $this->month . '/' . $this->day;
    }

    public function saveFile($showCmdOutput)
    {
        $fullpath = $this->createFile($this->_command);
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

    public function createFile($command)
    {
        $command = $this->cleanDeviceName($command);
        //create the file
        $filename = $this->_createFileName($command);
        $fullpath = $this->todayfolder . '/' . $filename;
        // create category dir based on hostname if not already made
        if (!is_dir($this->catfolder)) {
            mkdir($this->catfolder);
            custom_chown($this->catfolder);
        }
        // create hostname dir based on hostname if not already made
        if (!is_dir($this->hostfolder)) {
            mkdir($this->hostfolder);
            custom_chown($this->hostfolder);
        }
        // create todays dir.name based on this years date if not already made
        if (!is_dir($this->yearfolder)) {
            mkdir($this->yearfolder);
            custom_chown($this->yearfolder);
        }
        // create todays dir.name based on this months date if not already made
        if (!is_dir($this->monthfolder)) {
            mkdir($this->monthfolder);
            custom_chown($this->monthfolder);
        }
        // create todays dir.name based on todays date if not already made
        if (!is_dir($this->todayfolder)) {
            mkdir($this->todayfolder);
            custom_chown($this->todayfolder);
        }
        // if'' to create the filename based on the command if not created & chmod to 666
        if (!file_exists($fullpath)) {
            exec('touch ' . $fullpath);
            chmod($fullpath, 0666);
        }

        return (string) $fullpath;
    }

    /**
     * Function insertFileContents
     *
     * @param  string  $lines Command output from device
     * @param  string  $fullpath Fullpath as return by createFile Function to main script
     */
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

    /**
     * Function appendFileContents
     *
     * @param  string  $lines Command output from device
     * @param  string  $fullpath Fullpath as return by createFile Function to main script
     */
    //    public static function appendFileContents($lines, $fullpath) {
    //        // if the file is alread in place chmod it to 666 before writing info
    //        chmod($fullpath, 0666);
    //        // dump array into file & chmod back to RO
    //        $filehandle = fopen($fullpath, 'a');
    //        file_put_contents($fullpath, $lines);
    //        fclose($filehandle);
    //        chmod($fullpath, 0444);
    //    }
    /**
     * Function private _createFileName
     *
     * @param  string  $command Command from device
     * @param  string  $filename Filename after removing spaces and appending '.txt'
     */
    private function _createFileName($command)
    {
        $timestamp = date('Gi'); // format 1301
        // Create file name and return it
        $filename = str_replace(' ', '', $command) . '_' . $timestamp . '.txt';

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
        return implode(PHP_EOL, $showCmd);
    }
}
