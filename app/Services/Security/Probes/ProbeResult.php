<?php

namespace App\Services\Security\Probes;

/**
 * One self-probe response: status and size only, never the body.
 */
final class ProbeResult
{
    public function __construct(
        public readonly string $url,
        public readonly int $status,
        public readonly int $length,
        public readonly bool $matched,
    ) {}

    /**
     * Served with a 2xx and the body proved it was the real file, not the SPA's catch-all page.
     */
    public function isExposed(): bool
    {
        return $this->status >= 200 && $this->status < 300 && $this->matched;
    }

    public function describe(): string
    {
        return "{$this->url} returned {$this->status}";
    }
}
