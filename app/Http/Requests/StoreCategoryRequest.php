<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreCategoryDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
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
                'categoryName' => 'required|unique:categories|max:255|alpha_dash',
                'categoryDescription' => 'required| max:255',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'categoryName' => 'required|max:255|alpha_dash',
                'categoryDescription' => 'required| max:255',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreCategoryDTO
     */
    public function toDTO(): StoreCategoryDTO
    {
        return new StoreCategoryDTO([
            'categoryName' => $this->categoryName,
            'categoryDescription' => $this->categoryDescription,
        ]);
    }
}
