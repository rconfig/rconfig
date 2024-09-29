<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreUserDTO extends DtoBase
{
    public string $name;
    public $username;
    public string $email;
    public string $password;
    public string $role;
    

    public function __construct(array $parameters = [])
    {
        $this->name = $parameters['name'];
        $this->username = $parameters['username'];
        $this->email = $parameters['email'];
        $this->password = $parameters['password'];
        $this->role = $parameters['role'];
       }
}
