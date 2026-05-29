<?php

namespace App\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FilterMultipleFields implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $fields = array_map('trim', explode(',', $property));

        foreach ($fields as $field) {
            $query->orWhere($field, 'LIKE', '%' . $value . '%');
        }
    }
}
