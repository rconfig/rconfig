<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreDeviceCredentialDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceCredentialsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }


    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'cred_name' => 'required|min:3|unique:device_credentials|max:255',
                'cred_username' => 'required|min:3|max:255',
                'cred_password' => 'required|min:3|max:255',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'command' => 'required|min:3|max:255',
            ];
        }

        return $rules;
    }

    public function toDTO(): StoreDeviceCredentialDTO
    {
        return new StoreDeviceCredentialDTO([
            'cred_name' => $this->cred_name,
            'cred_description' => $this->cred_description ?? null,
            'cred_username' => $this->cred_username,
            'cred_password' => $this->cred_password,
            'cred_enable_password' => $this->cred_password ?? null,
        ]);
    }
}
