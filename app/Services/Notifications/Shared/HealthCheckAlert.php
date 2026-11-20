<?php

namespace App\Services\Notifications\Shared;

use App\Enums\NotificationType;
use Spatie\Health\Checks\Result;

/**
 * One health-check alert: the checks that started failing (or recovered) in a single
 * health:check run. Both channels (in-app and mail) read its title, summary and
 * per-check lines from here so they say the same thing. The security kind carries
 * Security Review transitions through the same channels.
 */
final class HealthCheckAlert
{
    public const PANEL_PATH = '/dashboard';

    public const SECURITY_PANEL_PATH = '/settings/security';

    public const KIND_HEALTH = 'health';

    public const KIND_SECURITY = 'security';

    /**
     * @param  array<int, array{name: string, label: string, status: string, summary: string, message: string}>  $checks
     */
    public function __construct(
        public readonly bool $recovered,
        public readonly array $checks,
        public readonly string $kind = self::KIND_HEALTH,
    ) {}

    /**
     * @param  array<int, Result>  $results
     */
    public static function fromResults(bool $recovered, array $results): self
    {
        return new self($recovered, array_map(fn (Result $result): array => [
            'name' => $result->check->getName(),
            'label' => $result->check->getLabel(),
            'status' => (string) $result->status->value,
            'summary' => $result->getShortSummary(),
            'message' => $result->getNotificationMessage(),
        ], array_values($results)));
    }

    /**
     * @param  array<int, array{name: string, label: string, status: string, summary: string, message: string}>  $checks
     */
    public static function security(bool $recovered, array $checks): self
    {
        return new self($recovered, $checks, self::KIND_SECURITY);
    }

    public function isSecurity(): bool
    {
        return $this->kind === self::KIND_SECURITY;
    }

    public function type(): NotificationType
    {
        if ($this->isSecurity()) {
            return $this->recovered
                ? NotificationType::SYSTEM_SECURITY_CHECK_RECOVERED
                : NotificationType::SYSTEM_SECURITY_CHECK_FAILED;
        }

        return $this->recovered
            ? NotificationType::SYSTEM_HEALTH_CHECK_RECOVERED
            : NotificationType::SYSTEM_HEALTH_CHECK_FAILED;
    }

    public function title(): string
    {
        $noun = $this->isSecurity() ? 'Security Check' : 'Health Check';

        return $noun . ($this->recovered ? ' Recovered' : ' Failed');
    }

    /**
     * "health checks" or "security checks", for summary and mail lines.
     */
    public function checksNoun(): string
    {
        return $this->isSecurity() ? 'security checks' : 'health checks';
    }

    public function summary(): string
    {
        $labels = implode(', ', $this->checkLabels());
        $checks = ucfirst($this->checksNoun());

        return $this->recovered
            ? $checks . ' passing again on ' . config('app.name') . ': ' . $labels . '.'
            : $checks . ' failing on ' . config('app.name') . ': ' . $labels . '.';
    }

    /**
     * @return array<int, string>
     */
    public function checkLabels(): array
    {
        return array_map(fn (array $check): string => $check['label'], $this->checks);
    }

    /**
     * One line per check, e.g. "Redis: Could not connect to Redis".
     *
     * @return array<int, string>
     */
    public function checkLines(): array
    {
        return array_map(function (array $check): string {
            $detail = $this->recovered ? ($check['summary'] ?: 'ok') : ($check['message'] ?: $check['summary']);

            return $detail === '' ? $check['label'] : $check['label'] . ': ' . $detail;
        }, $this->checks);
    }

    public function panelLabel(): string
    {
        return $this->isSecurity() ? 'View Security Review' : 'View Dashboard';
    }

    public function panelUrl(): string
    {
        return url($this->isSecurity() ? self::SECURITY_PANEL_PATH : self::PANEL_PATH);
    }
}
