<script setup>
import ConfigSearchFilterCardDateRangePicker from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCardDateRangePicker.vue';
import DeviceMultiSelect from '@/pages/Shared/FormFields/DeviceMultiSelect.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { inject, ref } from 'vue';
import { useCompareFilterCard } from './useCompareFilterCard';

defineProps({
  isLoading: Boolean,
  comparePosition: String
});
const emit = defineEmits(['updateConfigFilter']);
const formatters = inject('formatters');

const { clearAll, commands, model, performFilter, setDates } = useCompareFilterCard(emit);
</script>

<template>
  <div>
    <Card class="m-2 overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5">
          <CardTitle class="flex items-center gap-2 text-lg group">{{ formatters.capitalize(comparePosition) }} Device</CardTitle>
        </div>
      </CardHeader>
      <CardContent class="p-4 pt-0 text-sm">
        <div
          class="space-y-2"
          v-if="isLoading">
          <Skeleton class="w-1/2 h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
          <Skeleton class="w-full h-4" />
        </div>

        <transition name="fade">
          <div
            class="grid gap-3"
            v-if="!isLoading">
            <div class="grid items-center">
              <DeviceMultiSelect
                :singleSelect="true"
                v-model="model.device"
                id="selectedModelArray"
                class="w-full" />
              <!-- <Input
                v-model="model.device_name"
                id="device_name"
                autocomplete="off"
                placeholder="Device Name"
                class="w-full mb-1 focus:outline-none focus-visible:ring-0" /> -->
              <p class="ml-1 text-xs text-muted-foreground">
                Select a device for the left side (Required)
                <span class="text-red-400">*</span>
              </p>
            </div>

            <div class="grid items-center">
              <Select v-model="model.selectedCommand">
                <SelectTrigger class="w-full">
                  <SelectValue
                    class="placeholder:text-muted-foreground"
                    placeholder="Select a command" />
                </SelectTrigger>
                <SelectContent class="w-full mb-1 focus:outline-none focus-visible:ring-0">
                  <SelectGroup>
                    <SelectItem
                      v-for="command in commands"
                      :value="command.command">
                      {{ command.command }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <p class="ml-1 text-xs text-muted-foreground">Optionally select a command to filter the results</p>
            </div>

            <div class="flex flex-col items-start w-full space-x-2">
              <ConfigSearchFilterCardDateRangePicker
                class="w-full"
                @dateChange="setDates($event)" />
              <p class="ml-1 text-xs text-muted-foreground">Set date range. Last 7 days by default</p>
            </div>
          </div>
        </transition>
      </CardContent>
      <CardFooter class="flex flex-row items-center justify-end px-4 py-2 border-t bg-muted/50">
        <Button
          variant="outline"
          @click="clearAll()"
          class="flex items-center h-6 px-2 py-1 ml-2 text-xs text-muted-foreground"
          v-if="!isLoading">
          Clear
        </Button>
        <Button
          variant="primary"
          @click="performFilter(comparePosition)"
          class="h-6 px-2 py-1 ml-2 text-xs bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          v-if="!isLoading">
          Filter
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>
