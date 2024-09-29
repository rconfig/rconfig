<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreDeviceDTO extends DtoBase
{
    public string $device_name;
    public string $device_ip;
    public $device_port_override;
    public $device_vendor;
    public $device_default_creds_on;
    public ?string $device_username;
    public $device_password;
    public $device_enable_password;
    public $device_cred_id;
    public $ssh_key_id;
    public ?string $device_main_prompt;
    public ?string $device_enable_prompt;
    public $device_category_id;
    public $device_template;
    public $device_tags;
    public string $device_model;
    public $device_version;
    public $device_added_by; // 9000 = integration user id for display purposes later
    public $status;
    public $last_seen;

    public function __construct(array $parameters = [])
    {
        $this->device_name = $parameters['device_name'];
        $this->device_ip = $parameters['device_ip'];
        $this->device_port_override = $parameters['device_port_override'] ?? null;
        $this->device_vendor = $parameters['device_vendor'];
        $this->device_default_creds_on = $parameters['device_default_creds_on'];
        $this->device_username = $parameters['device_username'];
        $this->device_password = $parameters['device_password'];
        $this->device_enable_password = $parameters['device_enable_password'];
        $this->device_cred_id = $parameters['device_cred_id'];
        $this->ssh_key_id = $parameters['ssh_key_id'];
        $this->device_main_prompt = $parameters['device_main_prompt'];
        $this->device_enable_prompt = $parameters['device_enable_prompt'];
        $this->device_category_id = $parameters['device_category_id'];
        $this->device_template = $parameters['device_template'];
        $this->device_tags = $parameters['device_tags'];
        $this->device_model = $parameters['device_model'];
        $this->device_version = $parameters['device_version'];
        $this->device_added_by = $parameters['device_added_by'];
        $this->status = $parameters['status'];
        $this->last_seen = $parameters['last_seen'];
    }
}
