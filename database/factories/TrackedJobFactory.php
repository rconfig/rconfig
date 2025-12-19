<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackedJob>
 */
class TrackedJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'trackable_id' => (string) Str::uuid(),
            'trackable_type' => 'App\\Jobs\\DownloadConfigNowJob',
            'queue' => '',
            'status' => 'started',
            'payload' => '{"uuid":"83244959-f757-43e0-9943-35513efcab0a","timeout":120,"tags":[],"id":"83244959-f757-43e0-9943-35513efcab0a","backoff":null,"pushedAt":"1664009910.4628","displayName":"App\\\\Jobs\\\\DownloadConfigNowJob","maxTries":5,"job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","failOnTimeout":false,"data":{"command":"O:26:\\"App\\\\Jobs\\\\DownloadConfigNowJob\\":2:{s:11:\\"\\u0000*\\u0000username\\";s:5:\\"admin\\";s:12:\\"\\u0000*\\u0000device_id\\";i:1001;}","commandName":"App\\\\Jobs\\\\DownloadConfigNowJob"},"retryUntil":null,"maxExceptions":null,"type":"job","attempts":4}',
            'command' => 'O:26:"App\\Jobs\\DownloadConfigNowJob":2:{s:11:" * username";s:5:"admin";s:12:" * device_id";i:1001;}',
            'device_id' => $this->faker->randomElement([1001, 1002, 1003, 1004]),
            'output' => null,
            'started_at' => '2022-09-24 09:58:32',
            'finished_at' => null,
        ];
    }
}
