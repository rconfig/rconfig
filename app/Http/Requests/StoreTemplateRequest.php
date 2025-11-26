<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreTemplateDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{

    public function authorize()
    {
        return auth()->check(); // returning true if user is logged in
    }

    public function rules()
    {
        if ($this->getMethod() == 'POST') {
            $rules = [
                'templateName' => 'required|max:255',
                'code' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'templateName' => 'required|max:255',
                'code' => 'required',
            ];
        }

        return $rules;
    }

    public function toDTO(): StoreTemplateDTO
    {
        return new StoreTemplateDTO([
            'fileName' => $this->fileName,
            'templateName' => $this->templateName,
            'description' => $this->description,
        ]);
    }

    public function formattedFilename(): string
    {
        $filename = $this->fileName;
        if (str_ends_with($filename, '.yml')) {
            $filename = substr($filename, 0, -4);
        }

        return $filename . '.yml';
    }
}
