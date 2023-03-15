<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreCategoryDTO extends DataTransferObject
{
    public string $categoryName;

    public string $categoryDescription;
}
