<?php

namespace App\Http\Controllers\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

// Used to filter DeviceModels that have associated devices app/Http/Controllers/Api/DeviceModelController.php
class WithDevicesFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        if ($value) {
            return $query->has('devices');
        }

        return $query;
    }
}
