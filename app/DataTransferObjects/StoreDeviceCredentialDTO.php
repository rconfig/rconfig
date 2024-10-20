<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreDeviceCredentialDTO extends DtoBase
{
    public string $cred_name;
    public ?string $cred_description;
    public string $cred_username;
    public string $cred_password;
    public ?string $cred_enable_password;

    public function __construct(array $parameters = [])
    {
        $this->cred_name = $parameters['cred_name'];
        $this->cred_description = $parameters['cred_description'];
        $this->cred_username = $parameters['cred_username'];
        $this->cred_password = $parameters['cred_password'];
        $this->cred_enable_password = $parameters['cred_enable_password'];
    }
}
