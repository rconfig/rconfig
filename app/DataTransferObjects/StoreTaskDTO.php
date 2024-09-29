<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreTaskDTO extends DtoBase
{
    public $id;
    public $download_report_notify;
    public $verbose_download_report_notify;
    public $task_categories;
    public $task_devices;
    public $task_email_notify;
    public $task_tags;
    public int $is_system;
    public string $task_command;
    public string $task_cron;
    public ?string $task_desc;
    public string $task_name;

    public function __construct(array $parameters = [])
    {
        $this->id = $parameters['id'];
        $this->download_report_notify = $parameters['download_report_notify'];
        $this->verbose_download_report_notify = $parameters['verbose_download_report_notify'];
        $this->task_categories = $parameters['task_categories'];
        $this->task_devices = $parameters['task_devices'];
        $this->task_email_notify = $parameters['task_email_notify'];
        $this->task_tags = $parameters['task_tags'];
        $this->is_system = $parameters['is_system'];
        $this->task_command = $parameters['task_command'];
        $this->task_cron = $parameters['task_cron'];
        $this->task_desc = $parameters['task_desc'] !== null ? $parameters['task_desc'] : '';
        $this->task_name = $parameters['task_name'];
        
    }
}