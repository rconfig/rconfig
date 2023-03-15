<?php

namespace App\Http\Requests;

use App\DataTransferObjects\StoreTaskDTO;
use App\Rules\TaskDownloadCategoryHasCategories;
use App\Rules\TaskDownloadDeviceHasDevices;
use App\Rules\TaskDownloadTagHasTags;
use App\Rules\TaskPurgeDoesNotHaveDaysValue;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
        if (isset($this->request->all()['device'])) {
            $this->merge([
                'device' => collect($this->request->all()['device'])->pluck('id')->toArray(),
            ]);
        }
        if (isset($this->request->all()['task_tags'])) {
            $this->merge([
                'tag' => collect($this->request->all()['task_tags'])->pluck('id')->toArray(),
            ]);
        }
        if (isset($this->request->all()['category'])) {
            $this->merge([
                'category' => collect($this->request->all()['category'])->pluck('id')->toArray(),
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
        if ($this->getMethod() == 'POST') {
            $rules = [
                'task_name' => 'required|min:3|unique:tasks|max:255',
                'task_desc' => 'required|min:3',
                'task_command' => 'required',
                'task_cron' => 'required|array|between:4,5',
                'device' => new TaskDownloadDeviceHasDevices($this->request->get('task_command')),
                'category' => new TaskDownloadCategoryHasCategories($this->request->get('task_command')),
                'tag' => new TaskDownloadTagHasTags($this->request->get('task_command')),
                'task_tags' => new TaskDownloadTagHasTags($this->request->get('task_command')),
                'purge_days' => new TaskPurgeDoesNotHaveDaysValue($this->request->get('task_command')),
            ];
        }

        return $rules;
    }

    /**
     * Build and return a DTO.
     *
     * @return StoreTaskDTO
     */
    public function toDTO(): StoreTaskDTO
    {
        $cronPattern = $this->_getCronPattern(
            $this->task_cron[0],
            $this->task_cron[1],
            $this->task_cron[2],
            $this->task_cron[3],
            $this->task_cron[4]
        );

        return new StoreTaskDTO([
            'id' => mt_rand(100000, 999999),
            'task_name' => $this->task_name,
            'task_desc' => $this->task_desc,
            'task_command' => $this->task_command,
            'task_categories' => empty($this->category) ? 0 : 1,
            'task_devices' => empty($this->device) ? 0 : 1,
            'task_tags' => empty($this->tag) ? 0 : 1,
            'task_cron' => $cronPattern,
            'task_email_notify' => $this->task_email_notify,
            'download_report_notify' => $this->download_report_notify,
            'verbose_download_report_notify' => $this->verbose_download_report_notify,
            'is_system' => 0,
        ]);
    }

    private function _getCronPattern($minute, $hour, $day, $month, $weekday)
    {
        return $minute . ' ' . $hour . ' ' . $day . ' ' . $month . ' ' . $weekday . ' ';
    }
}
