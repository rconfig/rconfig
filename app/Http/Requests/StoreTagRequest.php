<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreTagDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
                'tagname' => 'required|unique:tags| max:50',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'tagname' => 'required|max:50',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreTagDTO
     */
    public function toDTO(): StoreTagDTO
    {
        return new StoreTagDTO([
            'tagname' => $this->tagname,
            'tagDescription' => $this->tagDescription,
        ]);
    }
}
