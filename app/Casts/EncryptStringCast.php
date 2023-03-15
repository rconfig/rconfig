<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class EncryptStringCast implements CastsAttributes
{
    //https://github.com/laravel/framework/pull/34937/commits/cf6768bddb9f616d60b931ddc82fc140c18a2aec
    // Bug #100 - v5 Passwords are not displaying correctly after upgrading to v6

    public function get($model, string $key, $value, array $attributes)
    {
        $value = ! is_null($value) ? \Crypt::decryptString($value) : null;

        if ($this->is_serialized($value)) {
            $value = unserialize($value);
        }

        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [$key => ! is_null($value) ? \Crypt::encryptString($value) : null];
    }

    private function is_serialized($string)
    {
        try {
            unserialize($string);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
