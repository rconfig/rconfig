<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreTemplateDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
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
                'fileName' => 'required|max:255',
                'code' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'fileName' => 'required|max:255',
                'code' => 'required',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreTemplateDTO
     */
    public function toDTO(): StoreTemplateDTO
    {
        return new StoreTemplateDTO([
            'fileName' => $this->fileName,
            'templateName' => $this->templateName,
            'description' => $this->description,
        ]);
    }
}
