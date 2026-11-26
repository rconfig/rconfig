<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingsPurgeRequest;
use App\Models\SettingsPurge;
use App\Services\DataPurge\TableStats;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class SettingsPurgeController extends Controller
{
    use RespondsWithHttpStatus;

    /**
     * Purge settings with approximate row counts and table sizes attached.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->perPage ?: 25;

        $result = QueryBuilder::for(SettingsPurge::class)
            ->allowedFilters(...[
                AllowedFilter::custom('q', new FilterMultipleFields, 'target'),
                AllowedFilter::callback('enabled_only', function (Builder $query): void {
                    $query->enabled();
                }),
            ])
            ->allowedSorts(...[
                'id',
                'target',
                'retention_days',
                'created_at',
                'updated_at',
                AllowedSort::callback('enabled', function (Builder $query, bool $descending): void {
                    $query->orderByRaw(SettingsPurge::ENABLED_SQL . ($descending ? ' desc' : ' asc'));
                }),
            ])
            ->defaultSort('target')
            ->paginate($perPage);

        $stats = TableStats::forTables($result->getCollection()->pluck('target')->all(), 20);

        $result->getCollection()->transform(function (SettingsPurge $setting) use ($stats): SettingsPurge {
            $row = $stats[$setting->target] ?? ['rows' => 0, 'bytes' => 0];

            $setting->total_rows = $row['rows'];
            $setting->total_bytes = $row['bytes'];
            $setting->total_bytes_hum = TableStats::humanBytes($row['bytes']);

            return $setting;
        });

        return response()->json($result);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(SettingsPurge::findOrFail($id));
    }

    /**
     * Options are merged into the stored ones, so a partial update never drops a key such as the
     * config retention extras.
     */
    public function update(int $id, StoreSettingsPurgeRequest $request): JsonResponse
    {
        $setting = SettingsPurge::findOrFail($id);
        $validated = $request->validated();

        $setting->update([
            'retention_days' => $validated['retention_days'],
            'options' => array_merge($setting->options ?? [], $validated['options']),
        ]);

        return $this->successResponse('Purge setting updated successfully!', ['id' => $setting->id]);
    }
}
