<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreCommandDTO extends DtoBase
{
    public string $command;
    public ?string $description;
    public ?bool $base64;

    public function __construct(array $parameters = [])
    {
        $this->command = $parameters['command'];
        $this->description = $parameters['description'];
        $this->base64 = isset($parameters['base64']) ? (bool) $parameters['base64'] : null;
    }
}
