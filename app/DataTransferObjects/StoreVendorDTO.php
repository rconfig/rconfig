<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreVendorDTO extends DataTransferObject
{
    public string $vendorName;
}
