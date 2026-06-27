<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\ConfigController as BaseConfigController;
use App\Models\Command;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

/**
 * @group Configurations
 *
 * @authenticated
 */
class ConfigsController extends BaseConfigController
{
    /**
     * Search stored configurations for a term, returning matching lines with context.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'searchTerm' => 'nullable|string|min:3',
            'search_term' => 'nullable|string|min:3',
            'devices' => 'sometimes|array',
            'devices.*' => 'integer',
            'commands' => 'sometimes|array',
            'dateFrom' => 'sometimes|date_format:Y-m-d',
            'dateTo' => 'sometimes|date_format:Y-m-d',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date' => 'sometimes|date_format:Y-m-d',
            'case_sensitive' => 'sometimes|boolean',
            'limit' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first());
        }

        $searchTerm = $request->input('searchTerm') ?? $request->input('search_term');
        if (empty($searchTerm)) {
            return $this->failureResponse('searchTerm is required');
        }

        $dateFrom = $request->input('dateFrom') ?? $request->input('from_date');
        $dateTo = $request->input('dateTo') ?? $request->input('to_date');
        $caseSensitive = $request->boolean('case_sensitive', false);
        $limit = (int) ($request->input('limit') ?? 0);

        $query = Config::query();

        if (! empty($request->input('devices', []))) {
            $query->whereIn('device_id', $request->input('devices'));
        }

        $commandFilters = $request->input('commands', []);
        if (! empty($commandFilters)) {
            $commandStrings = $this->resolveCommandFilters($commandFilters);
            if (empty($commandStrings)) {
                return $this->successResponse('Search results', []);
            }
            $query->whereIn('command', $commandStrings);
        }

        if (! empty($dateFrom) || ! empty($dateTo)) {
            $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
            $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('created_at', '>=', $from);
            } elseif ($to) {
                $query->where('created_at', '<=', $to);
            }
        }

        $results = [];
        foreach ($query->orderBy('created_at', 'desc')->get() as $config) {
            $matches = $this->findMatchesInConfig($config, $searchTerm, $caseSensitive);
            if (empty($matches)) {
                continue;
            }

            $results[] = [
                'id' => $config->id,
                'device_id' => $config->device_id,
                'config_location' => $config->config_location,
                'config_filename' => $config->config_filename,
                'config_command' => $config->command,
                'config_date' => $config->created_at ? Carbon::parse($config->created_at)->toDateString() : null,
                'device' => $config->device,
                'matches' => $matches,
            ];

            if ($limit > 0 && count($results) >= $limit) {
                break;
            }
        }

        return $this->successResponse('Search results', $results);
    }

    /**
     * Return all configurations for a device id, optionally filtered by download status.
     */
    public function allByDeviceId(int|string $id, string $status = 'all'): JsonResponse
    {
        $statusValue = $this->resolveStatus($status);
        if ($statusValue === false) {
            return $this->failureResponse('Invalid status');
        }

        if ($id !== 'all' && Config::where('device_id', $id)->count() === 0) {
            return $this->failureResponse('Configs not found for this device ID.');
        }

        $query = Config::query();
        if ($id !== 'all') {
            $query->where('device_id', $id);
        }
        if ($statusValue !== '%') {
            $query->where('download_status', $statusValue);
        }

        $configs = $query->get();
        foreach ($configs as $config) {
            try {
                $config['config'] = mb_convert_encoding(File::get($config['config_location']), 'UTF-8');
            } catch (\Exception $e) {
                $config['config'] = $e->getMessage();
            }
        }

        return $this->successResponse('Success', $configs);
    }

    /**
     * Count configurations for a device id filtered by download status.
     */
    public function statuscount(int|string $deviceid, string $status): JsonResponse
    {
        $statusValue = $this->resolveStatus($status);
        if ($statusValue === false) {
            return $this->failureResponse('Invalid status');
        }

        if ($deviceid !== 'all' && Config::where('device_id', $deviceid)->count() === 0) {
            return $this->failureResponse('Configs not found for this device ID.');
        }

        $query = Config::query();
        if ($deviceid !== 'all') {
            $query->where('device_id', $deviceid);
        }
        if ($statusValue !== '%') {
            $query->where('download_status', $statusValue);
        }

        return $this->successResponse(
            'Config status count for device ID ' . $deviceid . ' with status ' . $status,
            ['count' => $query->count()]
        );
    }

    private function resolveStatus(string $status): int|string|false
    {
        return match ($status) {
            'failed' => 0,
            'success' => 1,
            'unknown' => 2,
            'all' => '%',
            default => false,
        };
    }

    /**
     * @param  array<int, mixed>  $commandFilters
     * @return array<int, string>
     */
    private function resolveCommandFilters(array $commandFilters): array
    {
        $commandIds = [];
        $commandStrings = [];

        foreach ($commandFilters as $commandFilter) {
            if (is_numeric($commandFilter)) {
                $commandIds[] = (int) $commandFilter;

                continue;
            }
            if (is_string($commandFilter) && $commandFilter !== '') {
                $commandStrings[] = $commandFilter;
            }
        }

        if (! empty($commandIds)) {
            $commandStrings = array_merge(
                $commandStrings,
                Command::whereIn('id', $commandIds)->pluck('command')->all()
            );
        }

        return array_values(array_unique(array_filter($commandStrings)));
    }

    /**
     * @return array<int, array{line_number: int, line_text: string, context: array<int, string>}>
     */
    private function findMatchesInConfig(Config $config, string $searchTerm, bool $caseSensitive): array
    {
        if (empty($config->config_location) || ! file_exists($config->config_location)) {
            return [];
        }

        try {
            $fileContents = File::get($config->config_location);
        } catch (\Exception $e) {
            return [];
        }

        $lines = explode("\n", $fileContents);
        $needle = $caseSensitive ? $searchTerm : mb_strtolower($searchTerm);
        $matches = [];
        $totalLines = count($lines);

        foreach ($lines as $index => $line) {
            $haystack = $caseSensitive ? $line : mb_strtolower($line);
            if ($needle === '' || $haystack === '') {
                continue;
            }

            $found = $caseSensitive
                ? str_contains($haystack, $needle)
                : stripos($haystack, $needle) !== false;

            if ($found) {
                $start = max(0, $index - 2);
                $end = min($totalLines - 1, $index + 2);

                $matches[] = [
                    'line_number' => $index + 1,
                    'line_text' => $line,
                    'context' => array_slice($lines, $start, $end - $start + 1),
                ];
            }
        }

        return $matches;
    }
}
