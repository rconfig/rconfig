<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreTemplateDTO extends DataTransferObject
{
    public string $fileName;

    public string $templateName;

    public string $description;
}
