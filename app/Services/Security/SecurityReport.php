<?php

namespace App\Services\Security;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * A full rconfig:security-check run, serialised as JSON schema v1.
 */
final class SecurityReport
{
    public const SCHEMA_VERSION = 1;

    public const PRODUCT = 'core';

    /**
     * @param  list<SecurityReportItem>  $items
     * @param  array<string, mixed>  $host
     */
    public function __construct(
        public readonly CarbonImmutable $ranAt,
        public readonly string $trigger,
        public readonly string $version,
        public readonly array $items,
        public readonly int $durationMs,
        public readonly array $host = [],
    ) {}

    /**
     * @return Collection<int, SecurityReportItem>
     */
    public function collect(): Collection
    {
        return collect($this->items);
    }

    /**
     * @return array{pass: int, warn: int, fail: int, skip: int, total: int}
     */
    public function summary(): array
    {
        $summary = ['pass' => 0, 'warn' => 0, 'fail' => 0, 'skip' => 0, 'total' => count($this->items)];

        foreach ($this->items as $item) {
            $summary[$item->status->value]++;
        }

        return $summary;
    }

    /**
     * 0 when nothing needs action, 1 for warnings only, 2 for any failure.
     */
    public function exitCode(): int
    {
        $summary = $this->summary();

        return match (true) {
            $summary['fail'] > 0 => 2,
            $summary['warn'] > 0 => 1,
            default => 0,
        };
    }

    public function find(string $id): ?SecurityReportItem
    {
        return $this->collect()->first(fn (SecurityReportItem $item): bool => $item->id === $id);
    }

    /**
     * Items sorted for display: fail, warn, skip, pass; then severity; then ID.
     *
     * @return list<SecurityReportItem>
     */
    public function sortedItems(): array
    {
        return $this->collect()
            ->sort(fn (SecurityReportItem $a, SecurityReportItem $b): int => [$b->status->weight(), $b->severity->weight(), $a->id] <=> [$a->status->weight(), $a->severity->weight(), $b->id])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'schema' => self::SCHEMA_VERSION,
            'product' => self::PRODUCT,
            'version' => $this->version,
            'ran_at' => $this->ranAt->toIso8601String(),
            'trigger' => $this->trigger,
            'duration_ms' => $this->durationMs,
            'host' => $this->host === [] ? (object) [] : $this->host,
            'summary' => $this->summary(),
            'checks' => array_map(fn (SecurityReportItem $item): array => $item->toArray(), $this->sortedItems()),
        ];
    }

    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): string
    {
        return (string) json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Rebuilds a report from its stored JSON (security_check_runs.results).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ranAt: CarbonImmutable::parse((string) ($data['ran_at'] ?? 'now')),
            trigger: (string) ($data['trigger'] ?? SecurityContext::TRIGGER_CLI),
            version: (string) ($data['version'] ?? ''),
            items: array_map(fn (array $row): SecurityReportItem => SecurityReportItem::fromArray($row), array_values((array) ($data['checks'] ?? []))),
            durationMs: (int) ($data['duration_ms'] ?? 0),
            host: (array) ($data['host'] ?? []),
        );
    }
}
