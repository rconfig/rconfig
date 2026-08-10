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
        $rules = [];

        /**
         * fileName is derived server side from templateName, so a caller never needs
         * to send a path. Reject traversal sequences outright rather than trusting
         * every downstream consumer to sanitize.
         */
        $fileNameRules = ['nullable', 'string', 'max:255', 'not_regex:/\.\./'];

        if ($this->getMethod() == 'POST') {
            $rules = [
                'templateName' => 'required|max:255',
                'code' => 'required',
                'fileName' => $fileNameRules,
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'templateName' => 'required|max:255',
                'code' => 'required',
                'fileName' => $fileNameRules,
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
