<script setup>
import CategoryMultiSelect from '@/pages/Shared/FormFields/CategoryMultiSelect.vue';
import { FormControl, FormDescription, FormField, FormItem, FormLabel } from '@/components/ui/form';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';

import { ref, defineEmits } from 'vue';

defineProps({
  model: Object
});
</script>

<template>
  <div>
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Task Configuration</h3>

    <CategoryMultiSelect
      v-if="model.task_command === 'categories'"
      @update:modelValue="model.categoryArray = $event"
      :inboundCats="model.category" />

    <div>
      <h3 class="mt-4 mb-2 text-sm font-medium">Task Email Settings</h3>

      <div class="space-y-4">
        <div class="flex flex-row items-center justify-between p-4 space-y-2 border rounded-lg">
          <div class="space-y-0.5">
            <label
              for="radix-v-37-form-item"
              class="text-base font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
              Failure only report
            </label>
            <p
              id="radix-v-37-form-item-description"
              class="text-sm text-muted-foreground">
              Send task failure report only
            </p>
          </div>
          <Switch
            :checked="model.download_report_notify"
            @update:checked="model.download_report_notify = !model.download_report_notify" />
        </div>

        <div class="flex flex-row items-center justify-between p-4 space-y-2 border rounded-lg">
          <div class="space-y-0.5">
            <label
              for="radix-v-37-form-item"
              class="text-base font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
              Verbose report
            </label>
            <p
              id="radix-v-37-form-item-description"
              class="text-sm text-muted-foreground">
              Send verbose task report with both failures and successes
            </p>
          </div>
          <Switch
            :checked="model.verbose_download_report_notify"
            @update:checked="model.verbose_download_report_notify = !model.verbose_download_report_notify" />
        </div>
      </div>
    </div>
  </div>
</template>
