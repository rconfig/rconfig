<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Command;
use App\Models\Config;
use App\Traits\RespondsWithHttpStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ConfigSearchController extends Controller
{
    use RespondsWithHttpStatus;

    private const DEFAULT_SEARCH_LIMIT = 50;

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'searchTerm' => 'nullable|string|min:1',
            'search_term' => 'nullable|string|min:1',
            'search_string' => 'nullable|string|min:1',
            'criteria' => 'sometimes|array|min:1',
            'criteria.*.term' => 'required|string|min:1',
            'criteria_mode' => 'sometimes|in:all,any',
            'results_per_config' => 'sometimes|in:first_match,all_matches',
            'devices' => 'sometimes|array',
            'devices.*' => 'integer',
            'categories' => 'sometimes|array',
            'categories.*' => 'integer',
            'tags' => 'sometimes|array',
            'tags.*' => 'integer',
            'vendors' => 'sometimes|array',
            'vendors.*' => 'integer',
            'commands' => 'sometimes|array',
            'commands.*' => 'nullable',
            'command' => 'sometimes|nullable',
            'dateFrom' => 'sometimes|date_format:Y-m-d',
            'dateTo' => 'sometimes|date_format:Y-m-d',
            'from_date' => 'sometimes|date_format:Y-m-d',
            'to_date' => 'sometimes|date_format:Y-m-d',
            'latest_version_only' => 'sometimes|boolean',
            'ignore_case' => 'sometimes|boolean',
            'case_sensitive' => 'sometimes|boolean',
            'lines_before' => 'sometimes|integer|min:0|max:50',
            'lines_after' => 'sometimes|integer|min:0|max:50',
            'limit' => 'sometimes|integer|min:1',
            'page' => 'sometimes|integer|min:1',
            'perPage' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failureResponse($validator->errors()->first());
        }

        $criteria = $this->normalizeSearchCriteria($request);
        if (empty($criteria)) {
            return $this->failureResponse('At least one search term is required');
        }

        try {
            $dateFrom = $request->input('dateFrom') ?? $request->input('from_date');
            $dateTo = $request->input('dateTo') ?? $request->input('to_date');
            $caseSensitive = $request->has('case_sensitive')
                ? $request->boolean('case_sensitive', false)
                : ! $request->boolean('ignore_case', true);
            $criteriaMode = $request->input('criteria_mode', 'all');
            $resultsPerConfig = $request->input('results_per_config', 'first_match');
            $limit = max(1, (int) ($request->input('limit') ?? self::DEFAULT_SEARCH_LIMIT));
            $perPageInput = (int) ($request->input('perPage') ?? $request->input('per_page') ?? 0);
            $perPage = $perPageInput > 0 ? $perPageInput : $limit;
            $page = max(1, (int) ($request->input('page') ?? 1));
            $linesBefore = (int) ($request->input('lines_before') ?? 2);
            $linesAfter = (int) ($request->input('lines_after') ?? 2);

            $query = Config::query()->with('device');

            $devices = $request->input('devices', []);
            if (! empty($devices)) {
                $query->whereIn('device_id', $devices);
            }

            $commandFilters = $request->input('commands', []);
            if ($commandFilters === [] && $request->filled('command')) {
                $commandFilters = [$request->input('command')];
            }

            if (! empty($commandFilters)) {
                $commandStrings = $this->resolveCommandFilters($commandFilters);
                if (empty($commandStrings)) {
                    return response()->json(['success' => true, 'data' => []], 200);
                }
                $query->whereIn('command', $commandStrings);
            }

            $categories = $request->input('categories', []);
            if (! empty($categories)) {
                $query->whereHas('device.category', function ($categoryQuery) use ($categories) {
                    $categoryQuery->whereIn('categories.id', $categories);
                });
            }

            $tags = $request->input('tags', []);
            if (! empty($tags)) {
                $query->whereHas('device.tag', function ($tagQuery) use ($tags) {
                    $tagQuery->whereIn('tags.id', $tags);
                });
            }

            $vendors = $request->input('vendors', []);
            if (! empty($vendors)) {
                $query->whereHas('device.vendor', function ($vendorQuery) use ($vendors) {
                    $vendorQuery->whereIn('vendors.id', $vendors);
                });
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

            if ($request->boolean('latest_version_only', false)) {
                $query->where('latest_version', 1);
            }

            $configs = $query->orderBy('created_at', 'desc')->get();
            $results = [];
            $totalMatches = 0;
            $limitReached = false;
            $searchOptions = [
                'criteria' => $criteria,
                'criteria_mode' => $criteriaMode,
                'results_per_config' => $resultsPerConfig,
                'case_sensitive' => $caseSensitive,
                'lines_before' => $linesBefore,
                'lines_after' => $linesAfter,
            ];

            foreach ($configs as $config) {
                $matchData = $this->findMatchesInConfig($config, $searchOptions);
                if (empty($matchData)) {
                    continue;
                }

                $totalMatches += $matchData['match_count'];
                $results[] = $this->buildSearchResult($config, $matchData, $searchOptions);

                if (count($results) >= $limit) {
                    $limitReached = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $e->getMessage(), 'config');

            return $this->failureResponse('An error occurred while searching configurations. Check the logs.', 500);
        }

        $searchTerms = array_column($criteria, 'term');

        if ($this->shouldReturnLegacySearchFormat($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'search_term' => implode(' ', $searchTerms),
                    'search_terms' => $searchTerms,
                    'total_matches' => $totalMatches,
                    'matches_returned' => $totalMatches,
                    'results' => $results,
                ],
                'meta' => [
                    'limit' => $limit,
                    'results_returned' => count($results),
                    'limit_reached' => $limitReached,
                ],
            ], 200);
        }

        $total = count($results);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $effectivePage = min($page, $lastPage);
        $offset = ($effectivePage - 1) * $perPage;
        $pagedResults = array_slice($results, $offset, $perPage);

        return response()->json([
            'success' => true,
            'data' => $pagedResults,
            'meta' => [
                'limit' => $limit,
                'results_returned' => count($pagedResults),
                'limit_reached' => $limitReached,
                'current_page' => $effectivePage,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => $total > 0 ? $offset + count($pagedResults) : 0,
            ],
        ], 200);
    }

    private function shouldReturnLegacySearchFormat(Request $request): bool
    {
        return ($request->has('search_term') || $request->has('search_string'))
            && ! $request->has('searchTerm')
            && ! $request->has('criteria');
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
     * @return array<int, array{id: string|null, term: string}>
     */
    private function normalizeSearchCriteria(Request $request): array
    {
        $criteria = collect($request->input('criteria', []))
            ->map(function ($criterion) {
                $term = trim((string) ($criterion['term'] ?? ''));
                if ($term === '') {
                    return null;
                }

                return [
                    'id' => $criterion['id'] ?? null,
                    'term' => $term,
                ];
            })
            ->filter()
            ->unique(function (array $criterion) {
                return mb_strtolower($criterion['term']);
            })
            ->values()
            ->all();

        if (! empty($criteria)) {
            return $criteria;
        }

        $searchTerm = trim((string) ($request->input('searchTerm') ?? $request->input('search_term') ?? $request->input('search_string') ?? ''));
        if ($searchTerm === '') {
            return [];
        }

        return [[
            'id' => 'legacy-term',
            'term' => $searchTerm,
        ]];
    }

    /**
     * @param  array<string, mixed>  $searchOptions
     * @return array<string, mixed>
     */
    private function findMatchesInConfig(Config $config, array $searchOptions): array
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
        $matches = [];
        $totalLines = count($lines);
        $matchedTerms = [];
        $criteriaTerms = array_column($searchOptions['criteria'], 'term');
        $caseSensitive = $searchOptions['case_sensitive'];
        $linesBefore = $searchOptions['lines_before'];
        $linesAfter = $searchOptions['lines_after'];

        foreach ($lines as $index => $line) {
            $matchingTerms = $this->matchingTermsForLine($line, $criteriaTerms, $caseSensitive);
            if (empty($matchingTerms)) {
                continue;
            }

            foreach ($matchingTerms as $matchedTerm) {
                $matchedTerms[$matchedTerm] = true;
            }

            $start = max(0, $index - $linesBefore);
            $end = min($totalLines - 1, $index + $linesAfter);
            $context = array_slice($lines, $start, $end - $start + 1);

            $matches[] = [
                'line_number' => $index + 1,
                'line_text' => $line,
                'context' => $context,
                'matched_terms' => array_values($matchingTerms),
            ];
        }

        $matchedTerms = array_keys($matchedTerms);
        if (! $this->configMatchesCriteria($matchedTerms, $criteriaTerms, $searchOptions['criteria_mode'])) {
            return [];
        }

        return [
            'matches' => $matches,
            'preview_match' => $matches[0] ?? null,
            'matched_terms' => $matchedTerms,
            'match_count' => count($matches),
        ];
    }

    /**
     * @param  array<int, string>  $criteriaTerms
     * @return array<int, string>
     */
    private function matchingTermsForLine(string $line, array $criteriaTerms, bool $caseSensitive): array
    {
        return collect($criteriaTerms)
            ->filter(function (string $term) use ($line, $caseSensitive) {
                return $caseSensitive
                    ? mb_strpos($line, $term) !== false
                    : mb_stripos($line, $term) !== false;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $matchedTerms
     * @param  array<int, string>  $criteriaTerms
     */
    private function configMatchesCriteria(array $matchedTerms, array $criteriaTerms, string $criteriaMode): bool
    {
        if (empty($criteriaTerms)) {
            return false;
        }

        if ($criteriaMode === 'any') {
            return ! empty($matchedTerms);
        }

        $matchedLookup = collect($matchedTerms)
            ->mapWithKeys(fn (string $term) => [mb_strtolower($term) => true]);

        foreach ($criteriaTerms as $term) {
            if (! $matchedLookup->has(mb_strtolower($term))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $matchData
     * @param  array<string, mixed>  $searchOptions
     * @return array<string, mixed>
     */
    private function buildSearchResult(Config $config, array $matchData, array $searchOptions): array
    {
        $timestamp = $config->created_at ?? $config->start_time;
        $configDate = $timestamp ? Carbon::parse($timestamp)->toDateString() : null;
        $configTime = $timestamp ? Carbon::parse($timestamp)->format('H:i:s') : null;

        return [
            'id' => $config->id,
            'device_id' => $config->device_id,
            'file_id' => $config->id,
            'config_location' => $config->config_location,
            'config_filename' => $config->config_filename,
            'config_command' => $config->command,
            'command' => $config->command,
            'device_name' => $config->device_name,
            'device_category' => $config->device_category,
            'file' => $config->config_location,
            'config_filesize' => $config->config_filesize,
            'config_date' => $configDate,
            'config_time' => $configTime,
            'created_at' => $timestamp,
            'device' => $config->device,
            'matches' => $matchData['matches'],
            'preview_match' => $matchData['preview_match'],
            'match_count' => $matchData['match_count'],
            'matched_terms' => $matchData['matched_terms'],
            'criteria_mode' => $searchOptions['criteria_mode'],
            'results_per_config' => $searchOptions['results_per_config'],
        ];
    }
}
