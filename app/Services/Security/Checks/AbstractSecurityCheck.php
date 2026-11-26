<?php

namespace App\Services\Security\Checks;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Contracts\SecurityCheck;
use App\Services\Security\SecurityContext;

/**
 * Defaults shared by every security check. A check declares the properties below or overrides the methods.
 */
abstract class AbstractSecurityCheck implements SecurityCheck
{
    protected string $id = '';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = '';
    protected SecuritySeverity $severity = SecuritySeverity::Medium;
    protected string $rationale = '';

    /** @var list<string> */
    protected array $references = [];

    public function id(): string
    {
        return $this->id;
    }

    public function group(): SecurityCheckGroup
    {
        return $this->group;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function severity(): SecuritySeverity
    {
        return $this->severity;
    }

    public function rationale(): string
    {
        return $this->rationale;
    }

    public function appliesTo(SecurityContext $context): bool
    {
        return true;
    }

    public function references(): array
    {
        return $this->references;
    }

    public function docsUrl(): string
    {
        return rtrim((string) config('security-checks.checks_docs_url'), '/') . '/' . $this->group()->value . '/#' . str_replace(['.', '_'], '-', $this->id());
    }

    public function fixGuide(): string
    {
        return '';
    }

    /**
     * "Run: cd <app> && php artisan <command>" for fix lines.
     */
    protected function artisan(string $command, bool $sudo = false): string
    {
        return 'Run: ' . $this->artisanCommand($command, $sudo);
    }

    /**
     * "Run: cd <app> && sudo -u <web user> php artisan <command>", for commands that must not run as any other account.
     */
    protected function artisanAsWebUser(string $command): string
    {
        return 'Run: ' . rconfig_artisan_as_web_user($command);
    }

    /**
     * "cd <app> && [sudo] php artisan <command>".
     */
    protected function artisanCommand(string $command, bool $sudo = false): string
    {
        return 'cd ' . rconfig_appdir_path() . ' && ' . ($sudo ? 'sudo ' : '') . 'php artisan ' . $command;
    }

    /**
     * "1 device" / "3 devices".
     */
    protected function plural(int $count, string $singular, ?string $plural = null): string
    {
        return $count . ' ' . ($count === 1 ? $singular : ($plural ?? $singular . 's'));
    }

    /**
     * The verb that agrees with a plural() count: "1 token is" / "3 tokens are".
     */
    protected function verb(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? $singular : $plural;
    }
}
