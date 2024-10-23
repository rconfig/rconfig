<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreCommandDTO extends DtoBase
{
    public string $command;
    public ?string $description;

    public function __construct(array $parameters = [])
    {
        $this->command = $parameters['command'];
        $this->description = $parameters['description'];
    }
}
