<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TaskCronIsCorrectFormat implements Rule
{

    protected $task_command;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        // first validate is array
        if (!is_array($value)) {
            return false;
        }

        // validate that the cron array has 5 items
        if (count($value) !== 5) {
            return false;
        }

        // validate that the array does not have null values
        if (in_array(null, $value)) {
            return false;
        }

        // validate that the array does not have empty values
        if (in_array('', $value)) {
            return false;
        }

        // validate each array item has either a * or integer
        foreach ($value as $item) {
            if (!is_numeric($item) && $item !== '*') {
                return false;
            }
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
        return 'The cron format is incorrect.';
    }
}
