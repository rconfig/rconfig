<?php

namespace App\Models;

use Database\Factories\ConfigChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConfigChange extends Model
{
    /** @use HasFactory<ConfigChangeFactory> */
    use HasFactory;

    protected $fillable = [
        'current_config_id',
        'previous_config_id',
        'config_version',
        'config_change_type',
        'config_diff',
        'compare_exclusion_settings',
        'change_trigger',
    ];
    protected $with = ['currentConfig', 'previousConfig'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config_version' => 'integer',
            'compare_exclusion_settings' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function currentConfig(): HasOne
    {
        return $this->hasOne(Config::class, 'id', 'current_config_id');
    }

    public function previousConfig(): HasOne
    {
        return $this->hasOne(Config::class, 'id', 'previous_config_id');
    }
}
