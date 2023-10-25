<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DeviceIpIsValid implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if ($value == null) {
            return false;
        }

        if (filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The device ip must be a valid ip or FQDN/ Hostname.';
    }
}
