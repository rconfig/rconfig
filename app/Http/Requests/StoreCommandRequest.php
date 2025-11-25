<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreCommandDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommandRequest extends FormRequest
{

    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }

    protected function prepareForValidation()
    {
        if (isset($this->request->all()['category'])) {
            $this->merge([
                'categoryArray' => collect($this->request->all()['category'])->pluck('id')->toArray(),
            ]);
        }
    }

    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'command' => 'required|min:3|unique:commands|max:255',
                'categoryArray' => 'required|array|min:1',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'command' => 'required|min:3|max:255',
                'categoryArray' => 'required|array|min:1',
            ];
        }

        return $rules;
    }

    public function toDTO(): StoreCommandDTO
    {
        return new StoreCommandDTO([
            'command' => $this->command,
            'description' => $this->description,
            'category' => $this->category,
        ]);
    }
}
