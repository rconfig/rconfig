<?php

namespace App\CustomClasses;

use App\Models\Category;

class GetAndCheckCategoryIds
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function GetCategoryRecords()
    {
        return Category::with('device')->whereIn('id', $this->ids)->get();
    }
}
