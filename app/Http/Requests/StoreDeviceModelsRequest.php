<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreDeviceModelDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceModelsRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }

    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'name' => 'required|min:3|unique:device_models|max:255',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'name' => 'required|min:3|max:255',
            ];
        }

        return $rules;
    }

    public function toDTO(): StoreDeviceModelDTO
    {
        return new StoreDeviceModelDTO([
            'name' => $this->name,
        ]);
    }
}
