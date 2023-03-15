<?php

namespace App\CustomClasses;

use App\Models\Tag;

class GetAndCheckTagIds
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function GetTagRecords()
    {
        return Tag::with('device')->whereIn('id', $this->ids)->get();
    }
}
