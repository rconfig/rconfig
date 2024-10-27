<?php

namespace App\CustomClasses;

use App\Models\Category;
use App\Models\Config;
use App\Services\Config\FileOperations;

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
        $savedFileInfo = null;
        $device_category = Category::where('id', $this->devicerecord['device_category_id'])->pluck('categoryName')->first();
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
        }

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
        $model->save();
        if ($model->save() && !empty($savedFileInfo)) {
            return ['success' => true, 'commandName' => $this->commandName];
        }

        return ['success' => false, 'commandName' => $this->commandName];
    }
}
