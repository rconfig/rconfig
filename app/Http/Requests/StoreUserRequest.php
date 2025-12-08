<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|min:8',
                'repeat_password' => 'required|min:8',
                'role' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'repeat_password' => 'required|min:8',
                'role' => 'required',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreUserDTO
     */
    public function toDTO(): StoreUserDTO
    {
        return new StoreUserDTO([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'password' => \Hash::make($this->password),
            'role' => $this->role,
            'get_notifications' => $this->get_notifications ? true : false,
            'is_socialite' => $this->is_socialite ? true : false,
            'is_socialite_approved' => $this->is_socialite_approved ? true : false,
        ]);
    }
}
