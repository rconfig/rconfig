<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\DataTransferObjects\StoreDeviceModelDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceModelRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }

    protected function prepareForValidation()
    {
        // Trim whitespace from name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }

    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'name' => [
                    'required', 
                    'string',
                    'max:255',
                    'min:1',
                    Rule::unique('device_models', 'name'),
                    Rule::unique('devices', 'device_model')->where(function ($query) {
                        return $query->whereNotNull('device_model')->where('device_model', '!=', '');
                    }),
                ],
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:1',
                ],
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Device model name is required.',
            'name.unique' => 'This device model name already exists.',
            'name.max' => 'Device model name cannot exceed 255 characters.',
            'name.min' => 'Device model name must be at least 1 character.',
        ];
    }

    public function toDTO(): StoreDeviceModelDTO
    {
        return new StoreDeviceModelDTO([
            'name' => $this->name,
        ]);
    }
}
