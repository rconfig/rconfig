<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreSettingsEmailDTO extends DtoBase
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

    public function __construct(array $parameters = [])
    {
        $this->mail_host = $parameters['mail_host'];
        $this->mail_port = $parameters['mail_port'];
        $this->mail_from_email = $parameters['mail_from_email'];
        $this->mail_to_email = $parameters['mail_to_email'];
        $this->mail_authcheck = $parameters['mail_authcheck'];
        $this->mail_username = $parameters['mail_username'];
        $this->mail_password = $parameters['mail_password'];
        $this->mail_driver = $parameters['mail_driver'];
        $this->mail_encryption = $parameters['mail_encryption'];
    }
}
