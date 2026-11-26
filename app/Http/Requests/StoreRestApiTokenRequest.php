<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRestApiTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Creating a token is Admin only, because every token has full Admin rights
     * on the external API. The route carries the `admin` middleware as well; this
     * is a second, independent check so the rule does not rest on routing alone.
     */
    public function authorize(): bool
    {
        $actingUser = $this->user();

        return $actingUser !== null && $actingUser->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'api_token_name' => 'required|string|max:191|unique:rest_api_tokens,api_token_name',
        ];
    }
}
