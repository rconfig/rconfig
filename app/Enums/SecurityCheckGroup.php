<?php

namespace App\Enums;

/**
 * Groups of security checks. The value is the check ID prefix.
 */
enum SecurityCheckGroup: string
{
    case App = 'app';
    case Web = 'web';
    case Tls = 'tls';
    case Php = 'php';
    case Redis = 'redis';
    case Accounts = 'accounts';
    case Api = 'api';
    case Data = 'data';
    case Runtime = 'runtime';
    case Versions = 'versions';
    case Devices = 'devices';
    case Os = 'os';

    public function label(): string
    {
        return match ($this) {
            self::App => 'Application',
            self::Web => 'Web exposure',
            self::Tls => 'TLS',
            self::Php => 'PHP',
            self::Redis => 'Redis',
            self::Accounts => 'Accounts and sign-in',
            self::Api => 'REST API',
            self::Data => 'Data at rest',
            self::Runtime => 'Runtime and privileges',
            self::Versions => 'Versions',
            self::Devices => 'Devices',
            self::Os => 'Operating system',
        };
    }
}
