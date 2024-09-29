<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreVendorDTO extends DtoBase
{
    public string $vendorName;

    public function __construct(array $parameters = [])
    {
        $this->vendorName = $parameters['vendorName'];
    }
}
