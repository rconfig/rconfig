<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreSettingsTimezoneDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsTimezoneRequest extends FormRequest
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
                'timezone' => 'required|timezone',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'timezone' => 'required|timezone',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     */
    public function toDTO(): StoreSettingsTimezoneDTO
    {
        return new StoreSettingsTimezoneDTO([
            'timezone' => $this->timezone,
        ]);
    }
}
