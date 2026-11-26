<?php

namespace App\Http\Requests;

use App\Models\SettingsPurge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingsPurgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * The settings page sends options as a JSON string; an array is accepted as well.
     */
    protected function prepareForValidation(): void
    {
        $options = $this->input('options');

        if (is_string($options)) {
            $decoded = json_decode($options, true);

            $this->merge(['options' => is_array($decoded) ? $decoded : null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'retention_days' => 'required|integer|min:1|max:99999',
            'options' => 'required|array',
            'options.batch_size' => 'sometimes|integer|min:1|max:100000',
            'options.cleanup_frequency' => ['sometimes', Rule::in(SettingsPurge::CLEANUP_FREQUENCY_OPTIONS)],
            'options.enabled' => 'sometimes|boolean',
            'options.min_versions_per_device_command' => 'sometimes|integer|min:1|max:1000',
            'options.non_downloaded_retention_days' => 'sometimes|integer|min:1|max:99999',
        ];
    }

    /**
     * Only the known option keys are stored, with enabled as a real boolean.
     *
     * @param  array-key|null  $key
     * @param  mixed  $default
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        if (array_key_exists('enabled', $validated['options'] ?? [])) {
            $validated['options']['enabled'] = filter_var($validated['options']['enabled'], FILTER_VALIDATE_BOOLEAN);
        }

        return data_get($validated, $key, $default);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'retention_days.required' => 'The retention days field is required.',
            'retention_days.integer' => 'Retention days must be an integer.',
            'retention_days.min' => 'Retention days must be at least 1.',
            'retention_days.max' => 'Retention days cannot exceed 99999.',
            'options.required' => 'Options must be valid JSON.',
            'options.array' => 'Options must be valid JSON.',
            'options.batch_size.integer' => 'Batch size must be a positive integer.',
            'options.batch_size.min' => 'Batch size must be a positive integer.',
            'options.cleanup_frequency.in' => 'Cleanup frequency must be one of: ' . implode(', ', SettingsPurge::CLEANUP_FREQUENCY_OPTIONS) . '.',
            'options.enabled.boolean' => 'Enabled must be a boolean value.',
        ];
    }
}
