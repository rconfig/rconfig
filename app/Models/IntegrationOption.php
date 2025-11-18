<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'icon',
        'name',
        'type',
        'description',
        'action_text',
        'config_url',
        'external_url',
        'status',
    ];
}
