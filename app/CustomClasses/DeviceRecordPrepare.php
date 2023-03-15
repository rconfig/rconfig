<?php

namespace App\CustomClasses;

use App\Models\Category;

class DeviceRecordPrepare
{
    protected $devicerecord;

    public function __construct(object $devicerecord)
    {
        $this->devicerecord = $devicerecord;
    }

    public function DeviceRecordToArray()
    {
        $commands = $this->_getCommands($this->devicerecord->device_category_id);

        if ($commands->isEmpty()) {
            // this is useful for some tests or when a devices category has no commands
            return $commands[0] = [];
        }

        $devicerecordArr = $this->devicerecord->toArray();

        foreach ($commands[0]->command as $command) {
            $devicerecordArr['commands'][$command->id] = $command->command;
        }

        return $devicerecordArr;
    }

    private function _getCommands($device_category_id)
    {
        return Category::with('command')->where('id', $device_category_id)->get();
    }
}
