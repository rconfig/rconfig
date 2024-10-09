<?php

namespace App\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FilterMultipleFields implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $fields = array_map('trim', explode(',', $property));

        foreach ($fields as $field) {
            $query = $query->orWhere($field, 'LIKE', '%' . $value . '%');
        }

        return $query;
    }
}
