<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ReflectionClass;

/***
 * Class TrackedJob
 * @package Junges\TrackableJobs\Models
 * @mixin Builder
 */
class TrackedJob extends BaseModel
{
    use HasFactory;

    const STATUS_QUEUED = 'queued';

    const STATUS_STARTED = 'started';

    const STATUS_FINISHED = 'finished';

    const STATUS_FAILED = 'failed';

    protected $table = 'tracked_jobs';

    protected $keyType = 'int';

    protected $fillable = [
        'trackable_id',
        'trackable_type',
        'name',
        'status',
        'payload',
        'command',
        'device_id',
        'output',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function trackable(): MorphTo
    {
        return $this->morphTo('trackable', 'trackable_type', 'trackable_id');
    }

    public function markAsStarted(): bool
    {
        return $this->update([
            'status' => static::STATUS_STARTED,
            'started_at' => now(),
        ]);
    }

    public function markAsFinished(string $message = null): bool
    {
        if ($message) {
            $this->setOutput($message);
        }

        return $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => now(),
        ]);
    }

    public function markAsFailed(string $exception = null): bool
    {
        if ($exception) {
            $this->setOutput($exception);
        }

        return $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => now(),
        ]);
    }

    public function setPayload(array $payload): bool
    {
        $device_id = null;

        if ($payload['displayName'] === 'App\Jobs\DownloadConfigNowJob') {
            $device_id = $this->accessProtected(unserialize($payload['data']['command']), 'device_id');
        }
        if ($payload['displayName'] === 'App\Jobs\DeviceDownloadJob') {
            $device_id = $this->accessProtected(unserialize($payload['data']['command']), 'devicerecord');
            $device_id = $device_id->id;
        }
        // dump(unserialize($payload['data']['command']));
        return $this->update([
            'payload' => json_encode($payload),
            'command' => $payload['data']['command'] ? $payload['data']['command'] : null,
            'device_id' => $device_id ? $device_id : null,
        ]);
    }

    public function setOutput(string $output): bool
    {
        return $this->update([
            'output' => $output,
        ]);
    }

    /**
     * Whether the job has already started.
     *
     * @return bool
     */
    public function hasStarted(): bool
    {
        return ! empty($this->started_at);
    }

    /**
     * Get the duration of the job, in human diff.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getDurationAttribute(): string
    {
        if (! $this->hasStarted()) {
            return '';
        }

        return ($this->finished_at ?? now())
            ->diffAsCarbonInterval($this->started_at)
            ->forHumans(['short' => true]);
    }

    private function accessProtected($obj, $prop)
    {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
