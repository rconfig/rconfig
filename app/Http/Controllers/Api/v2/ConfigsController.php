<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\FilterMultipleFields;
use App\Http\Controllers\Api\v1\ConfigsController as V1ConfigsController;
use App\Models\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Configurations
 *
 * @authenticated
 */
class ConfigsController extends V1ConfigsController
{
    /**
     * Columns a caller may request with the `fields` query parameter.
     *
     * @var array<int, string>
     */
    private const SELECTABLE_FIELDS = [
        'id',
        'device_id',
        'device_name',
        'device_category',
        'command',
        'type',
        'download_status',
        'report_id',
        'config_location',
        'config_filename',
        'config_filesize',
        'config_hash',
        'config_version',
        'start_time',
        'end_time',
        'duration',
        'latest_version',
        'created_at',
        'updated_at',
    ];

    /**
     * List configs, paginated, newest first.
     *
     * @queryParam includeConfig boolean Attach the config text under a `config` key on each row. Example: true
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null): JsonResponse
    {
        $configs = QueryBuilder::for(Config::class)
            ->allowedFilters(...[
                AllowedFilter::custom('q', new FilterMultipleFields, 'id, device_name'),
                AllowedFilter::exact('download_status'),
                AllowedFilter::exact('device_id'),
                AllowedFilter::callback('device_name', function (Builder $query, mixed $value): void {
                    $query->where('device_name', 'LIKE', '%' . $value . '%');
                }),
                AllowedFilter::exact('command'),
                AllowedFilter::exact('latest_version'),
                AllowedFilter::callback('created_at', function (Builder $query, mixed $value): void {
                    $query->whereDate('created_at', $value);
                }),
                AllowedFilter::scope('created_at_between'),
            ])
            ->allowedFields(...self::SELECTABLE_FIELDS)
            ->defaultSort('-created_at')
            ->allowedSorts('id', 'device_name', 'command', 'download_status', 'created_at')
            ->paginate((int) ($request->perPage ?: 10))
            ->withQueryString();

        if ($request->boolean('includeConfig')) {
            foreach ($configs as $config) {
                $config['config'] = $this->readConfigContents($config);
            }
        }

        return response()->json($configs);
    }

    /**
     * Show a single config.
     *
     * @queryParam includeConfig boolean Attach the config text under a `config` key on the response. Example: true
     */
    public function show($id, $relationship = null, $withCount = null): JsonResponse
    {
        $config = Config::findOrFail($id);

        if (request()->boolean('includeConfig')) {
            $config['config'] = $this->readConfigContents($config);
        }

        return response()->json($config);
    }

    /**
     * Read a config's file from disk as UTF-8, returning an error message in its place when unreadable.
     */
    private function readConfigContents(Config $config): string
    {
        $location = $config->config_location ?? Config::whereKey($config->getKey())->value('config_location');

        if (empty($location) || ! File::exists($location)) {
            return 'File does not exist at path ' . $location;
        }

        try {
            return mb_convert_encoding(File::get($location), 'UTF-8');
        } catch (\Exception $e) {
            return 'Unable to read file ' . $e->getMessage();
        }
    }
}
