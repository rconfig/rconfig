<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreCommandDTO extends DataTransferObject
{
    public string $command;

    public string $description;
    // public array $categoryArray;
}
