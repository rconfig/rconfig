<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreDeviceDTO extends DataTransferObject
{
    public string $device_name;

    public string $device_ip;

    public $device_port_override;

    public $device_vendor;

    public $device_default_creds_on;

    public string $device_username;

    public $device_password;

    public $device_enable_password;

    public $device_cred_id;

    public $ssh_key_id;

    public string $device_main_prompt;

    public $device_enable_prompt;

    public $device_category_id;

    public $device_template;

    public $device_tags;

    public string $device_model;

    public $device_version;

    public $device_added_by;

    public $status;

    public $last_seen;
}
