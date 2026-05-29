<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final class StoreSettingsTimezoneDTO extends DtoBase
{
    public string $timezone;

    public function __construct(array $parameters = [])
    {
        $this->timezone = $parameters['timezone'];
    }
}
