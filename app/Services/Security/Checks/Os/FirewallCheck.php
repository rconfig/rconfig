<?php

namespace App\Services\Security\Checks\Os;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

class FirewallCheck extends AbstractSecurityCheck
{
    public const UNITS = ['firewalld', 'ufw', 'nftables', 'iptables'];

    protected string $id = 'os.firewall';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Os;
    protected string $title = 'A host firewall is active';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'A host firewall limits the server to HTTPS and SSH, so a database or Redis misconfiguration is not reachable from the network.';
    protected array $references = ['CIS RHEL 9 3.4', 'CIS Ubuntu 24.04 4.1', 'NIST SC-7', 'NIS2 21(2)(i)'];

    public function __construct(private readonly SystemServices $services) {}

    public function appliesTo(SecurityContext $context): bool
    {
        return ! $context->isDocker && PHP_OS_FAMILY === 'Linux';
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if (! $this->services->knows(self::UNITS)) {
            return SecurityCheckResult::skip('systemd did not answer, so the firewall state is unknown.');
        }

        $active = $this->services->anyActive(self::UNITS);

        if ($active !== null) {
            return SecurityCheckResult::pass("{$active} is active.", ['service' => $active]);
        }

        return SecurityCheckResult::fail(
            'No host firewall service (firewalld, ufw, nftables or iptables) is active.',
            $this->fixGuide(),
        );
    }

    public function fixGuide(): string
    {
        return 'RHEL family: sudo systemctl enable --now firewalld && sudo firewall-cmd --permanent --add-service=https --add-service=ssh && sudo firewall-cmd --reload. Ubuntu: sudo ufw allow OpenSSH && sudo ufw allow 443/tcp && sudo ufw enable.';
    }
}
