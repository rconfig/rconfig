<?php

namespace App\DataTransferObjects;

use ReflectionClass;
use ReflectionProperty;

class DtoBase
{
    /**
     * Convert the DTO to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        $values = [];

        foreach ($properties as $property) {
            $values[$property->getName()] = $property->getValue($this);
        }

        return $values;
    }
}
