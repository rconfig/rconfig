<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreTagDTO extends DataTransferObject
{
    public string $tagname;

    public string $tagDescription;
}
