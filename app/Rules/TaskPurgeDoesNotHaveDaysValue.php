<?php

namespace App\Rules;

use App\Traits\TaskLabelLookupTable;
use Illuminate\Contracts\Validation\Rule;

class TaskPurgeDoesNotHaveDaysValue implements Rule
{
    use TaskLabelLookupTable;

    protected $task_command;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($task_command)
    {
        $this->task_command = $task_command;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->task_command === 'rconfig:purge-configs' && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The '.$this->commandLookupTable($this->task_command).' task must have a value of days.';
    }
}
