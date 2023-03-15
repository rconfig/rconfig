<?php

namespace App\CustomClasses;

use App\Models\Task;

class GetAndCheckTaskIds
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function GetTaskRecords()
    {
        return Task::with('device', 'tag', 'category')->whereIn('id', $this->ids)->get();
    }
}
