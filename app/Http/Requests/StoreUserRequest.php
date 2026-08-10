<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Creating and editing accounts is Admin only. The routes carry the `admin`
     * middleware as well; this is a second, independent check so the rule does
     * not rest on routing alone.
     *
     * Checking only the requested role is not enough. An attacker does not need
     * to promote themselves to Admin: resetting an existing Admin's password
     * while sending role 'User' would take over that account instead. So the
     * acting user must be an Admin regardless of what role is being written.
     *
     * @return bool
     */
    public function authorize()
    {
        $actingUser = $this->user();

        return $actingUser !== null && $actingUser->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        if ($this->getMethod() == 'POST') {
            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|min:8',
                'repeat_password' => 'required|min:8',
                'role' => 'required|in:Admin,User',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
                'repeat_password' => 'required|min:8',
                'role' => 'required|in:Admin,User',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
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
