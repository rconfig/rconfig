<?php

namespace App\CustomClasses;

use App\Models\Category;
use App\Models\Command;
use App\Models\Config;
use App\Services\Config\FileOperations;

class SaveConfigsToDiskAndDb
{
    public $configsArray;
    public $type;
    public $commandName;
    public $devicerecord;
    public $report_id;
    public $model;
    public $command;
    public $latest_version;

    public function __construct($type, $commandName, $configsArray, $devicerecord, $report_id = null)
    {
        $this->type = $type;
        $this->commandName = $commandName;
        $this->configsArray = $configsArray;
        $this->devicerecord = $devicerecord;
        $this->report_id = $report_id;
        $this->model = new Config;
        $this->command = new Command;
        $this->latest_version = new Config;
    }

    public function saveConfigs()
    {
        $savedFileInfo = null;
        $device_category = Category::find($this->devicerecord['device_category_id'])->categoryName;
        $duration = (int) $this->devicerecord['start_time']->diffInSeconds($this->devicerecord['end_time']);

        if ($this->configsArray != 0 || $this->configsArray != null) {
            $fileops = new FileOperations(
                    $this->commandName,
                    $device_category,
                    $this->devicerecord['device_name'],
                    $this->devicerecord['id'],
                    config_data_path(),
                    $this->type
            );
            $savedFileInfo = $fileops->saveFile($this->configsArray);
        }

        Config::where('device_id', $this->devicerecord['id'])
                ->where('command', $this->commandName)
                ->update(['latest_version' => 0]);

        $this->model->device_id = $this->devicerecord['id'];
        $this->model->device_name = $this->devicerecord['device_name'];
        $this->model->device_category = $device_category;
        $this->model->command = $this->commandName;
        $this->model->type = $this->type;

        if ($this->configsArray === 0 || $this->configsArray === null) {
            $this->model->download_status = 0;
        } else {
            $this->model->config_location = $savedFileInfo['filepath'];
            $this->model->config_filename = $savedFileInfo['filename'];
            $this->model->config_filesize = $savedFileInfo['filesize'];
            $this->model->download_status = $savedFileInfo['download_status'];
        }

        $this->model->report_id = $this->report_id;
        $this->model->start_time = $this->devicerecord['start_time']->toDateTimeString();
        $this->model->end_time = $this->devicerecord['end_time']->toDateTimeString();
        $this->model->duration = $duration;
        $this->model->latest_version = 1;

        $saved = $this->model->save();

        if ($saved && ! empty($savedFileInfo)) {
            return ['success' => true, 'commandName' => $this->commandName];
        }

        return ['success' => false, 'commandName' => $this->commandName];
    }

    public function saveFailedConfigs()
    {
        $this->model->device_id = $this->devicerecord['id'];
        $this->model->device_name = $this->devicerecord['device_name'];
        $this->model->device_category = Category::find($this->devicerecord['device_category_id'])->categoryName;
        $this->model->command = $this->commandName;
        $this->model->type = $this->type;
        $this->model->download_status = 0;
        $this->model->report_id = $this->report_id;
        $this->model->start_time = $this->devicerecord['start_time']->toDateTimeString();
        $this->model->end_time = $this->devicerecord['end_time']->toDateTimeString();
        $this->model->duration = (int) $this->devicerecord['start_time']->diffInSeconds($this->devicerecord['end_time']);
        $this->model->latest_version = 1;
        $this->model->save();

        return ['success' => true, 'commandName' => $this->commandName];
    }
}
