<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeletePurgeRunsRequest;
use App\Models\PurgeRun;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PurgeRunController extends Controller
{
    use RespondsWithHttpStatus;

    private const LOAD_MORE_PAGE_SIZE = 50;

    private const ALL_ROWS_PER_PAGE = 10000000;

    public function index(Request $request): JsonResponse
    {
        $isLoadMoreMode = $request->boolean('loadMore');
        $perPage = (int) $request->perPage ?: 10;

        if ($isLoadMoreMode && $perPage >= self::ALL_ROWS_PER_PAGE) {
            $perPage = self::LOAD_MORE_PAGE_SIZE;
        }

        $response = QueryBuilder::for(PurgeRun::class)
            ->allowedFilters(...[
                AllowedFilter::callback('q', function (Builder $query, mixed $value): void {
                    $term = is_array($value) ? implode(' ', $value) : $value;
                    $query->where(function (Builder $query) use ($term): void {
                        $query->where('scope', 'like', "%{$term}%")->orWhere('summary', 'like', "%{$term}%");
                    });
                }),
                AllowedFilter::partial('type'),
                AllowedFilter::partial('mode'),
                AllowedFilter::partial('status'),
                AllowedFilter::partial('source'),
                AllowedFilter::partial('message'),
                AllowedFilter::exact('dry_run'),
            ])
            ->defaultSort('-id')
            ->allowedSorts('id', 'type', 'mode', 'status', 'dry_run', 'source', 'started_at', 'finished_at', 'created_at')
            ->paginate($perPage);

        if ($isLoadMoreMode) {
            $response->appends(['loadMore' => true]);
        }

        return response()->json($response);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(PurgeRun::findOrFail($id));
    }

    public function destroy(int $id): JsonResponse
    {
        PurgeRun::findOrFail($id)->delete();

        return $this->successResponse('Purge run deleted successfully!');
    }

    public function deleteMany(DeletePurgeRunsRequest $request): JsonResponse
    {
        PurgeRun::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Purge runs deleted successfully'], 200);
    }
}
