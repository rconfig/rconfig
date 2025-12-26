<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final class StoreSettingsDeviceCredDTO extends DtoBase
{
    public string $defaultDeviceUsername;
    public string $defaultDevicePassword;
    public string $defaultEnablePassword;

    public function __construct(array $parameters = [])
    {
        $this->defaultDeviceUsername = $parameters['defaultDeviceUsername'];
        $this->defaultDevicePassword = $parameters['defaultDevicePassword'];
        $this->defaultEnablePassword = $parameters['defaultEnablePassword'];
    }
}
