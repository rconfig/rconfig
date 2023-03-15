<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreVendorDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
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
                'vendorName' => 'required|unique:vendors| max:255',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'vendorName' => 'required|max:255',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreVendorDTO
     */
    public function toDTO(): StoreVendorDTO
    {
        return new StoreVendorDTO([
            'vendorName' => $this->vendorName,
        ]);
    }
}
