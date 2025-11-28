<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Lorisleiva\CronTranslator\CronExpression;

class TaskCronIsCorrectFormat implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        // first validate is array
        if (! is_array($value)) {
            $fail('The cron format is incorrect.');

            return;
        }

        // validate that the cron array has 5 items
        if (count($value) !== 5) {
            $fail('The cron format is incorrect.');

            return;
        }

        // validate that the array does not have null values
        if (in_array(null, $value)) {
            $fail('The cron format is incorrect.');

            return;
        }

        // validate that the array does not have empty values
        if (in_array('', $value)) {
            $fail('The cron format is incorrect.');

            return;
        }

        // validate its a cron expression
        $cron = implode(' ', $value);
        try {
            $cron = new CronExpression($cron);
        } catch (\Exception $e) {
            $fail('The cron format is incorrect.');

            return;
        }

    }
}
