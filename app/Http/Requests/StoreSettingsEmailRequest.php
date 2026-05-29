<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreSettingsEmailDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingsEmailRequest extends FormRequest
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
                'deviceEmailging' => 'required',
                'phpEmailging' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            if ($this->mail_authcheck === true || $this->mail_authcheck === 'true') {
                $rules = [
                    'mail_host' => 'required|min:3|max:255',
                    'mail_port' => 'required|min:1|max:255',
                    'mail_from_email' => 'required|min:3|max:255|email',
                    'mail_to_email' => 'required|min:3|max:255',
                    'mail_encryption' => 'required|min:3|max:255',
                    'mail_verify_peer' => 'nullable|boolean',
                    'mail_auto_tls' => 'nullable|boolean',
                ];
            } else {
                $rules = [
                    'mail_host' => 'required|min:3|max:255',
                    'mail_port' => 'required|min:1|max:255',
                    'mail_from_email' => 'required|min:3|max:255|email',
                    'mail_to_email' => 'required|min:3|max:255',
                    'mail_verify_peer' => 'nullable|boolean',
                    'mail_auto_tls' => 'nullable|boolean',
                ];
            }
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     */
    public function toDTO(): StoreSettingsEmailDTO
    {
        return new StoreSettingsEmailDTO([
            'mail_host' => $this->mail_host,
            'mail_port' => (int) $this->mail_port,
            'mail_from_email' => $this->mail_from_email,
            'mail_to_email' => $this->mail_to_email,
            'mail_authcheck' => $this->mail_authcheck,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->mail_password,
            'mail_driver' => $this->mail_driver,
            'mail_encryption' => $this->mail_authcheck === false ? null : $this->mail_encryption,
            'mail_verify_peer' => (int) $this->boolean('mail_verify_peer'),
            'mail_auto_tls' => (int) $this->boolean('mail_auto_tls'),
        ]);
    }
}
