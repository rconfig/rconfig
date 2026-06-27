<?php

namespace App\Models;

use App\Casts\EncryptStringCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestApiToken extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_token' => EncryptStringCast::class,
        ];
    }
}
