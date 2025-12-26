<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreSettingsDeviceCredDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsDeviceCredRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'defaultDeviceUsername' => 'required|min:3|max:255',
                'defaultDevicePassword' => 'required|min:3|max:255',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'defaultDeviceUsername' => 'required|min:3|max:255',
                'defaultDevicePassword' => 'required|min:3|max:255',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     */
    public function toDTO(): StoreSettingsDeviceCredDTO
    {
        return new StoreSettingsDeviceCredDTO([
            'defaultDeviceUsername' => $this->defaultDeviceUsername,
            'defaultDevicePassword' => $this->defaultDevicePassword,
            'defaultEnablePassword' => $this->defaultEnablePassword,
        ]);
    }
}
