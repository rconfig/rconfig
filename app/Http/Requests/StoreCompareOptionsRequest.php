<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompareOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'context' => 'required|integer',
            'lengthLimit' => 'required|integer',
            'ignoreCase' => 'required|boolean',
            'ignoreLineEnding' => 'required|boolean',
            'ignoreWhitespace' => 'required|boolean',
            'config_compare_exclusion_file' => 'nullable|string',
        ];
    }
}
