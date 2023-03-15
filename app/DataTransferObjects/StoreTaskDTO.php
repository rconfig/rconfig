<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

final class StoreTaskDTO extends DataTransferObject
{

    public $download_report_notify;

    public $task_categories;

    public $task_devices;

    public $task_email_notify;

    public $task_tags;

    public $verbose_download_report_notify;

    public $id;

    public int $is_system;

    public string $task_command;

    public string $task_cron;

    public string $task_desc;

    public string $task_name;
}
