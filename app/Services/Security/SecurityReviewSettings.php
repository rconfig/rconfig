<?php

namespace App\Services\Security;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

/**
 * The Security Review alert switch, stored in settings.security_check_notify (null uses the config default).
 */
class SecurityReviewSettings
{
    private const SETTINGS_ID = 1;

    public function notifyEnabled(): bool
    {
        $stored = $this->hasColumn() ? Setting::query()->whereKey(self::SETTINGS_ID)->value('security_check_notify') : null;

        return $stored === null ? (bool) config('security-checks.notify', true) : (bool) $stored;
    }

    public function setNotify(bool $enabled): void
    {
        Setting::query()->whereKey(self::SETTINGS_ID)->update(['security_check_notify' => $enabled]);
    }

    private function hasColumn(): bool
    {
        return Schema::hasColumn('settings', 'security_check_notify');
    }
}
