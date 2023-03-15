<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreSettingsBannerDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsBannerRequest extends FormRequest
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
                'login_banner' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'login_banner' => 'required',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreSettingsBannerDTO
     */
    public function toDTO(): StoreSettingsBannerDTO
    {
        return new StoreSettingsBannerDTO([
            'login_banner' => $this->login_banner,
        ]);
    }
}
