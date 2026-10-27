<?php

namespace App\Services\Notifications\Shared;

use App\Enums\NotificationType;
use Spatie\Health\Checks\Result;

/**
 * One health-check alert: the checks that started failing (or recovered) in a single
 * health:check run. Both channels (in-app and mail) read its title, summary and
 * per-check lines from here so they say the same thing.
 */
final class HealthCheckAlert
{
    public const PANEL_PATH = '/dashboard';

    /**
     * @param  array<int, array{name: string, label: string, status: string, summary: string, message: string}>  $checks
     */
    public function __construct(
        public readonly bool $recovered,
        public readonly array $checks,
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

    public function type(): NotificationType
    {
        return $this->recovered
            ? NotificationType::SYSTEM_HEALTH_CHECK_RECOVERED
            : NotificationType::SYSTEM_HEALTH_CHECK_FAILED;
    }

    public function title(): string
    {
        return $this->recovered ? 'Health Check Recovered' : 'Health Check Failed';
    }

    public function summary(): string
    {
        $labels = implode(', ', $this->checkLabels());

        return $this->recovered
            ? 'Health checks passing again on ' . config('app.name') . ': ' . $labels . '.'
            : 'Health checks failing on ' . config('app.name') . ': ' . $labels . '.';
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

    public function panelUrl(): string
    {
        return url(self::PANEL_PATH);
    }
}
