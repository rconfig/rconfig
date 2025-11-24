<?php

namespace App\Http\Controllers\QueryFilters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Filters\Filter;

class QueryFilterMultipleFields implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $fields = array_map('trim', explode(',', $property));
        $value = '%' . strtolower($value) . '%';

        // Create a grouped where clause to properly handle OR conditions
        $query->where(function ($subQuery) use ($fields, $value) {
            foreach ($fields as $field) {
                $this->handleMySqlField($subQuery, $field, $value);
            }
        });

        return $query;
    }

    /**
     * Handle MySQL field filtering (simpler as type coercion is automatic)
     *
     * @param  Builder  $query  The query builder instance
     * @param  string  $field  The field name to filter on
     * @param  string  $value  The search value with wildcards
     */
    private function handleMySqlField(Builder $query, string $field, string $value): void
    {
        $query->orWhere($field, 'LIKE', $value);
    }
}
