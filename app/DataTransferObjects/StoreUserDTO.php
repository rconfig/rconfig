<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreUserDTO extends DataTransferObject
{
    public string $name;

    public $username;

    public string $email;

    public string $password;

    public string $role;
}
