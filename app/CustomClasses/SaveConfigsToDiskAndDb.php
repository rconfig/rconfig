<?php

namespace App\CustomClasses;

use App\Models\Category;
use App\Models\Config;
use App\Services\Config\FileOperations;
use Exception;
use Illuminate\Support\Facades\Log;

class SaveConfigsToDiskAndDb
{
    public $configsArray;

    public $type;

    public $commandName;

    public $devicerecord;

    public $report_id;

    public function __construct($type, $commandName, $configsArray, $devicerecord, $report_id = null)
    {
        $this->type = $type;
        $this->commandName = $commandName;
        $this->configsArray = $configsArray;
        $this->devicerecord = $devicerecord;
        $this->report_id = $report_id;
    }

    public function saveConfigs()
    {
        try {
            if (empty($this->devicerecord)) {
                throw new Exception('Device record is empty or invalid.');
            }

            if (empty($this->commandName)) {
                throw new Exception('Command name is not provided.');
            }

            if (!isset($this->devicerecord['device_category_id'])) {
                throw new Exception('Device category ID is missing in the device record.');
            }

            if (!isset($this->devicerecord['start_time']) || !isset($this->devicerecord['end_time'])) {
                throw new Exception('Start time or end time is missing in the device record.');
            }

            $savedFileInfo = null;
            $device_category = Category::where('id', $this->devicerecord['device_category_id'])->pluck('categoryName')->first();

            if (empty($device_category)) {
                throw new Exception('Device category not found.');
            }

            $duration = $this->devicerecord['start_time']->diffInSeconds($this->devicerecord['end_time']);

            if ($this->configsArray != 0) {
                $fileops =
                    new FileOperations(
                        $this->commandName,
                        $device_category,
                        $this->devicerecord['device_name'],
                        $this->devicerecord['id'],
                        config_data_path(),
                        $this->type
                    );
                $savedFileInfo = $fileops->saveFile($this->configsArray);

                if (empty($savedFileInfo) || !isset($savedFileInfo['filepath'], $savedFileInfo['filename'], $savedFileInfo['filesize'], $savedFileInfo['download_status'])) {
                    throw new Exception('Error saving configuration file. File information is incomplete.');
                }
            }

            // Update all other records with the same device_id and command to set latest_version to 0
            Config::where('device_id', $this->devicerecord['id'])
                ->where('command', $this->commandName)
                ->update(['latest_version' => 0]);

            $model = new Config;
            $model->device_id = $this->devicerecord['id'];
            $model->device_name = $this->devicerecord['device_name'];
            $model->device_category = $device_category;
            $model->command = $this->commandName;
            $model->type = $this->type;
            if ($this->configsArray === 0 || $this->configsArray === null) {
                $model->download_status = 0;
            } else {
                $model->config_location = $savedFileInfo['filepath'];
                $model->config_filename = $savedFileInfo['filename'];
                $model->config_filesize = $savedFileInfo['filesize'];
                $model->download_status = $savedFileInfo['download_status'];
            }
            $model->report_id = $this->report_id;
            $model->start_time = $this->devicerecord['start_time']->toDateTimeString();
            $model->end_time = $this->devicerecord['end_time']->toDateTimeString();
            $model->duration = $duration;
            $model->latest_version = 1;

            if (!$model->save()) {
                throw new Exception('Error saving configuration data to the database.');
            }

            return ['success' => true, 'commandName' => $this->commandName];
        } catch (Exception $e) {
            Log::error('Error in SaveConfigsToDiskAndDb: ' . $e->getMessage());
            return ['success' => false, 'commandName' => $this->commandName, 'error' => $e->getMessage()];
        }
    }
}
