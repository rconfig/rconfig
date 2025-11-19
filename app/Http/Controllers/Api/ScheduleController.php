<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleController extends Controller
{
    public function list()
    {
        try {
            // Get the schedule instance from the application container
            $schedule = app(Schedule::class);

            $events = collect($schedule->events())->map(function ($event) {
                return [
                    'command' => $event->command ?? $event->getSummaryForDisplay(),
                    'expression' => $event->expression,
                    'description' => $event->description ?? 'No description',
                    'next_run' => $event->nextRunDate()->format('Y-m-d H:i:s'),
                    'timezone' => $event->timezone ?? config('app.timezone'),
                ];
            });

            return response()->json([
                'success' => true,
                'scheduled_tasks' => $events,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
