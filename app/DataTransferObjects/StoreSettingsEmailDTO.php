<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreSettingsEmailDTO extends DataTransferObject
{
    public string $mail_host;

    public int $mail_port;

    public string $mail_from_email;

    public string $mail_to_email;

    public $mail_authcheck;

    public $mail_username;

    public $mail_password;

    public $mail_driver;

    public $mail_encryption;
}
