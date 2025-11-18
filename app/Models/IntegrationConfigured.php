<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class IntegrationConfigured extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public function integrationoption()
    {
        return $this->belongsTo(IntegrationOption::class, 'integration_option_id', 'id');
    }
}
