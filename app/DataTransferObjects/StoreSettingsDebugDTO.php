<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final class StoreSettingsDebugDTO extends DtoBase
{
    public bool $deviceDebugging;
    public bool $phpDebugging;

    public function __construct(array $parameters = [])
    {
        $this->deviceDebugging = $parameters['deviceDebugging'];
        $this->phpDebugging = $parameters['phpDebugging'];
    }
}
