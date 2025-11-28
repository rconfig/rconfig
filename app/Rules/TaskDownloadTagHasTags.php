<?php

namespace App\Rules;

use App\Traits\TaskLabelLookupTable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TaskDownloadTagHasTags implements ValidationRule
{
    use TaskLabelLookupTable;

    protected $task_command;

    public function __construct($task_command)
    {
        $this->task_command = $task_command;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->task_command === 'rconfig:download-tag' && empty($value)) {
            $fail('The ' . $this->commandLookupTable($this->task_command) . ' task must have tags associated with it. Please select at least one tag.');
        }

    }
}
