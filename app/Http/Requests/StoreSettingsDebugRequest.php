<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreSettingsDebugDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsDebugRequest extends FormRequest
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
                'deviceDebugging' => 'required',
                'phpDebugging' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'deviceDebugging' => 'required',
                'phpDebugging' => 'required',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     */
    public function toDTO(): StoreSettingsDebugDTO
    {
        return new StoreSettingsDebugDTO([
            'deviceDebugging' => $this->deviceDebugging,
            'phpDebugging' => $this->phpDebugging,
        ]);
    }
}
