<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final class StoreDeviceModelDTO extends DtoBase
{
    public string $name;

    public function __construct(array $parameters = [])
    {
        $this->name = $parameters['name'];
    }
}
