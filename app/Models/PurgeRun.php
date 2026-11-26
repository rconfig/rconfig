<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurgeRun extends BaseModel
{
    use HasFactory;

    protected $table = 'purge_runs';
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dry_run' => 'boolean',
            'scope' => 'array',
            'summary' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
