<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreCategoryDTO extends DtoBase
{
    public string $categoryName;
    public ?string $categoryDescription;
    public string $badgeColor;

    public function __construct(array $parameters = [])
    {
        $this->categoryName = $parameters['categoryName'];
        $this->categoryDescription = $parameters['categoryDescription'];
        $this->badgeColor = $parameters['badgeColor'];
    }
}
