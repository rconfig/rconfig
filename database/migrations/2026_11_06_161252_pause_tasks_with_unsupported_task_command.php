<?php

use App\Jobs\TaskDownloadRun;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Pause any task whose task_command is not one of the supported scheduled
 * commands. The scheduler already skips these tasks; pausing them makes that
 * visible in the UI. Rows are kept so an operator can review or delete them.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        DB::table('tasks')
            ->where(function ($query) {
                $query->whereNull('is_paused')->orWhere('is_paused', false);
            })
            ->where(function ($query) {
                $query->whereNull('task_command')
                    ->orWhereNotIn('task_command', TaskDownloadRun::ALLOWED_TASK_COMMANDS);
            })
            ->orderBy('id')
            ->get(['id', 'task_command'])
            ->each(function (object $task): void {
                DB::table('tasks')->where('id', $task->id)->update(['is_paused' => true]);

                Log::warning('Paused a task with an unsupported task command.', [
                    'task_id' => $task->id,
                    'task_command' => $task->task_command,
                ]);
            });
    }

    /**
     * Reverse the migrations. Paused tasks are left paused.
     */
    public function down(): void
    {
        //
    }
};
