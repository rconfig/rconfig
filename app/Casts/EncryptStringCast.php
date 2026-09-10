<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class EncryptStringCast implements CastsAttributes
{
    // https://github.com/laravel/framework/pull/34937/commits/cf6768bddb9f616d60b931ddc82fc140c18a2aec
    // Bug #100 - v5 Passwords are not displaying correctly after upgrading to v6

    public function get($model, string $key, $value, array $attributes)
    {
        $value = ! empty($value) ? Crypt::decryptString($value) : null;
        // If $value is null, skip further checks and return null
        if (is_null($value)) {
            return null;
        }

        // check if $value is spaces
        if (trim($value) === '') {
            $value = null;
        }

        if ($this->is_serialized($value)) {
            // allowed_classes => false blocks PHP object injection: any serialized object
            // decodes to __PHP_Incomplete_Class and no gadget __wakeup/__destruct fires. See #369.
            $unserialized = unserialize($value, ['allowed_classes' => false]);

            // This unserialize only exists to decode legacy serialized scalar passwords
            // (bug #100). A serialized object or array was never a valid secret, so never
            // surface one: keep the raw decrypted string instead of a broken incomplete
            // class or array that would later fail re-encryption.
            if (! is_object($unserialized) && ! is_array($unserialized)) {
                $value = $unserialized;
            }
        }

        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return [$key => ! is_null($value) ? Crypt::encryptString($value) : null];
    }

    private function is_serialized($string)
    {
        try {
            unserialize($string, ['allowed_classes' => false]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
