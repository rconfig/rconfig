<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreTemplateDTO extends DtoBase
{
    public string $fileName;
    public string $templateName;
    public ?string $description;

    public function __construct(array $parameters = [])
    {
        $this->fileName = $parameters['fileName'];
        $this->templateName = $parameters['templateName'];
        $this->description = $parameters['description'];
    }
}
