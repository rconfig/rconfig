<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreCommandDTO extends DtoBase
{
    public string $command;
    public ?string $description;
    public ?bool $base64;   // data is base64 encoded ?
    public ?string $ext;    // File Extension (to subtitute '.txt' default file extension)

    public function __construct(array $parameters = [])
    {
        $this->command = $parameters['command'];
        $this->description = $parameters['description'];
        $this->base64 = isset($parameters['base64']) ? (bool) $parameters['base64'] : null;
        $this->ext = $parameters['ext'];
    }
}
