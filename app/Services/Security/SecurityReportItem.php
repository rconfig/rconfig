<?php

namespace App\Services\Security;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecurityCheckStatus;
use App\Enums\SecuritySeverity;
use App\Services\Security\Contracts\SecurityCheck;

/**
 * One check's row in a security report: what was checked plus its result.
 */
final class SecurityReportItem
{
    /**
     * @param  list<string>  $references
     * @param  array<string, mixed>  $evidence
     */
    public function __construct(
        public readonly string $id,
        public readonly SecurityCheckGroup $group,
        public readonly string $title,
        public readonly SecuritySeverity $severity,
        public readonly SecurityCheckStatus $status,
        public readonly string $message,
        public readonly ?string $fix,
        public readonly array $evidence,
        public readonly string $docsUrl,
        public readonly array $references,
        public readonly string $rationale,
        public readonly int $durationMs,
    ) {}

    public static function fromCheck(SecurityCheck $check, SecurityCheckResult $result, int $durationMs): self
    {
        return new self(
            id: $check->id(),
            group: $check->group(),
            title: $check->title(),
            severity: $check->severity(),
            status: $result->status,
            message: $result->message,
            fix: $result->fix,
            evidence: $result->evidence,
            docsUrl: $check->docsUrl(),
            references: $check->references(),
            rationale: $check->rationale(),
            durationMs: $durationMs,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'group' => $this->group->value,
            'group_label' => $this->group->label(),
            'title' => $this->title,
            'severity' => $this->severity->value,
            'status' => $this->status->value,
            'message' => $this->message,
            'evidence' => $this->evidence === [] ? (object) [] : $this->evidence,
            'fix' => $this->fix,
            'docs_url' => $this->docsUrl,
            'references' => $this->references,
            'rationale' => $this->rationale,
            'duration_ms' => $this->durationMs,
        ];
    }

    /**
     * @param  array<string, mixed>  $row  a row written by toArray()
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            group: SecurityCheckGroup::tryFrom((string) ($row['group'] ?? '')) ?? SecurityCheckGroup::App,
            title: (string) ($row['title'] ?? $row['id']),
            severity: SecuritySeverity::tryFrom((string) ($row['severity'] ?? '')) ?? SecuritySeverity::Info,
            status: SecurityCheckStatus::tryFrom((string) ($row['status'] ?? '')) ?? SecurityCheckStatus::Skip,
            message: (string) ($row['message'] ?? ''),
            fix: isset($row['fix']) ? (string) $row['fix'] : null,
            evidence: (array) ($row['evidence'] ?? []),
            docsUrl: (string) ($row['docs_url'] ?? ''),
            references: array_values((array) ($row['references'] ?? [])),
            rationale: (string) ($row['rationale'] ?? ''),
            durationMs: (int) ($row['duration_ms'] ?? 0),
        );
    }
}
