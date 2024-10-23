<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreDeviceModelDTO extends DtoBase
{
    public string $name;

    public function __construct(array $parameters = [])
    {
        $this->name = $parameters['name'];
    }
}
