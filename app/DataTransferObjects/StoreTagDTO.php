<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreTagDTO extends DtoBase
{
    public string $tagname;
    public ?string $tagDescription;

    public function __construct(array $parameters = [])
    {
        $this->tagname = $parameters['tagname'];
        $this->tagDescription = $parameters['tagDescription'];
    }
}
