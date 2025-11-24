<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait MaskableCredentials
{
    public function maskCredsIfEnabled($result)
    {
        if (config('rConfig.mask_device_credentials')) {
            $result->device_username = $this->mask($result->device_username);
            $result->device_password = $this->mask($result->device_password);
            $result->device_enable_password = $this->mask($result->device_enable_password);
        }

        return $result;
    }

    public function credsAreMasked($result)
    {
        if (Str::contains($result['device_username'], '*') || Str::contains($result['device_password'], '*') || Str::contains($result['device_enable_password'], '*')) {
            return true;
        }

        return false;
    }

    public function mask($field)
    {
        return Str::mask($field, '*', 2);
    }

}
