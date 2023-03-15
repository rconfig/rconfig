<?php

namespace App\CustomClasses;

class FilterArgsForCommnds
{
    private $args;

    public function __construct()
    {
    }

    public function filterArgs($args)
    {
        $this->args = $args;

        return array_filter($this->args, 'ctype_digit');
    }
}
