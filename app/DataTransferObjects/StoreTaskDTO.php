<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\DataTransferObjects\DtoBase;

final class StoreTaskDTO extends DtoBase
{
    public $archive_type;
    public $archive_value;
    public $download_report_notify;
    public $purge_days;
    public $task_archive_logs;
    public $task_categories;
    public $task_devices;
    public $task_api;
    public $task_email_notify;
    public $task_policyassignment;
    public $task_snippet;
    public $task_tags;
    public $verbose_download_report_notify;
    public $id;
    public int $is_system;
    public string $task_command;
    public string $task_cron;
    public ?string $task_desc;
    public string $task_name;
    public bool|int $is_paused;
    public ?int $integration_id;

    public function __construct(array $parameters = [])
    {
        $this->archive_type = $parameters['archive_type'];
        $this->archive_value = $parameters['archive_value'];
        $this->download_report_notify = $parameters['download_report_notify'];
        $this->purge_days = $parameters['purge_days'];
        $this->task_archive_logs = $parameters['task_archive_logs'];
        $this->task_categories = $parameters['task_categories'];
        $this->task_devices = $parameters['task_devices'];
        $this->task_api = $parameters['task_api'];
        $this->task_email_notify = $parameters['task_email_notify'];
        $this->task_policyassignment = $parameters['task_policyassignment'];
        $this->task_snippet = $parameters['task_snippet'];
        $this->task_tags = $parameters['task_tags'];
        $this->verbose_download_report_notify = $parameters['verbose_download_report_notify'];
        $this->id = $parameters['id'];
        $this->is_system = $parameters['is_system'];
        $this->task_command = $parameters['task_command'];
        $this->task_cron = $parameters['task_cron'];
        $this->task_desc = $parameters['task_desc'] !== null ? $parameters['task_desc'] : '';
        $this->task_name = $parameters['task_name'];
        $this->is_paused = $parameters['is_paused'];
        $this->integration_id = $parameters['integration_id'];
    }
}
