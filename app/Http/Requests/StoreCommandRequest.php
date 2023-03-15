<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreCommandDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommandRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (isset($this->request->all()['categoryArray'])) {
            $this->merge([
                'categoryArray' => collect($this->request->all()['categoryArray'])->pluck('id')->toArray(),
            ]);
        }
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
                'command' => 'required|min:3|unique:commands|max:255',
                'description' => 'required',
                'categoryArray' => 'required|array|min:1',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'command' => 'required|min:3|max:255',
                'description' => 'required',
                'categoryArray' => 'required|array|min:1',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreCommandDTO
     */
    public function toDTO(): StoreCommandDTO
    {
        return new StoreCommandDTO([
            'command' => $this->command,
            'description' => $this->description,
            // 'categoryArray' => $this->categoryArray,
        ]);
    }
}
