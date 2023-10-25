<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreDeviceDTO;
use App\Rules\CategoryHasCommands;
use App\Rules\DeviceIpIsValid;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
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
        if (isset($this->request->all()['device_tags'])) {
            $this->merge([
                'device_tags' => collect($this->request->all()['device_tags'])->pluck('id'),
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
        // dd($this->request);
        if ($this->getMethod() == 'POST') {
            $rules = [
                'device_name' => 'required|unique:devices|min:5|max:255|regex:/^\S*$/u',
                'device_ip' => new DeviceIpIsValid,
                'device_port_override' => 'nullable|integer|min:1|max:65535',
                'device_vendor' => 'required',
                'device_model' => 'required|max:255|min:2',
                'device_category_id' => 'required|max:255',
                'device_category_id' => new CategoryHasCommands,
                'device_tags' => 'required',
                'device_username' => 'required|max:255|min:2',
                'device_password' => 'required|min:2',
                'device_template' => 'required',
                'device_main_prompt' => 'required',
            ];
        }

        if ($this->getMethod() == 'PATCH') {
            $rules = [
                'device_name' => 'required|min:5|max:255|alpha_dash',
                'device_ip' => 'required|ip',
                'device_vendor' => 'required',
                'device_model' => 'required|max:255|min:2',
                'device_category_id' => 'required|max:255',
                'device_category_id' => new CategoryHasCommands,
                'device_tags' => 'required|max:255',
                'device_username' => 'required|max:255|min:2',
                'device_password' => 'required|min:2',
                'device_template' => 'required',
                'device_main_prompt' => 'required',
                'device_enable_prompt' => '',
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreDeviceDTO
     */
    public function toDTO(): StoreDeviceDTO
    {
        return new StoreDeviceDTO([
            'device_name' => $this->device_name,
            'device_ip' => $this->device_ip,
            'device_port_override' => $this->device_port_override,
            'device_vendor' => $this->device_vendor,
            'device_default_creds_on' => $this->device_default_creds_on,
            'device_username' => $this->device_username,
            'device_password' => $this->device_password,
            'device_enable_password' => $this->device_enable_password,
            'device_cred_id' => $this->device_cred_id,
            'ssh_key_id' => $this->ssh_key_id,
            'device_main_prompt' => $this->device_main_prompt,
            'device_enable_prompt' => $this->device_enable_prompt,
            'device_category_id' => $this->device_category_id,
            'device_tags' => $this->device_tags,
            'device_template' => $this->device_template,
            'device_model' => $this->device_model,
            'device_version' => $this->device_version,
            'device_added_by' => $this->device_added_by,
            'status' => $this->status,
            'last_seen' => $this->last_seen,
        ]);
    }
}
