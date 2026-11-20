<?php

namespace App\Services\Security\Checks\Devices;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Http\Controllers\Connections\Params\ConnectionParams;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Counts devices whose connection template uses Telnet. Templates that cannot be read are reported, not guessed.
 */
class TelnetDevicesCheck extends AbstractSecurityCheck
{
    protected string $id = 'devices.telnet';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Devices;
    protected string $title = 'Devices are backed up over SSH, not Telnet';
    protected SecuritySeverity $severity = SecuritySeverity::Medium;
    protected string $rationale = 'Telnet sends the device username, password and enable password in clear text on every backup. Anyone on the path between rConfig and the device can capture them.';
    protected array $references = ['NIST SC-8', 'NIST IA-5', 'CIS Controls 12.2', 'NIS2 21(2)(h)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $perTemplate = DB::table('devices')
            ->whereNotNull('device_template')
            ->groupBy('device_template')
            ->selectRaw('device_template, COUNT(*) as total')
            ->pluck('total', 'device_template');

        $total = (int) DB::table('devices')->count();
        $telnet = 0;
        $unreadable = 0;

        foreach ($perTemplate as $templateId => $count) {
            $protocol = $this->protocolFor((int) $templateId);

            if ($protocol === null) {
                $unreadable += (int) $count;
            } elseif ($protocol === 'telnet') {
                $telnet += (int) $count;
            }
        }

        $evidence = ['devices' => $total, 'telnet' => $telnet, 'template_unreadable' => $unreadable];

        if ($telnet === 0) {
            return SecurityCheckResult::pass(match (true) {
                $total === 0 => 'No devices are set up yet.',
                $total === 1 => 'The only device does not use Telnet.',
                default => "None of the {$total} devices uses Telnet.",
            }, $evidence);
        }

        $share = $total > 0 ? (int) round($telnet / $total * 100) : 0;

        return SecurityCheckResult::warn(
            $this->plural($telnet, 'device') . " ({$share}%) " . $this->verb($telnet, 'is backed up over Telnet, which sends its', 'are backed up over Telnet, which sends their') . ' credentials in clear text.',
            $this->fixGuide(),
            $evidence,
        );
    }

    public function fixGuide(): string
    {
        return 'Enable SSH on these devices and move them to an SSH template. Keep Telnet only for devices that cannot do SSH, on a management network.';
    }

    private function protocolFor(int $templateId): ?string
    {
        try {
            $protocol = (new ConnectionParams($templateId))->getTemplateParams()['connect']['protocol'] ?? null;
        } catch (Throwable) {
            return null;
        }

        return is_string($protocol) ? strtolower($protocol) : null;
    }
}
