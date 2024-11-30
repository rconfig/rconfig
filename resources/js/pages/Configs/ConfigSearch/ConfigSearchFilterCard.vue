<script setup>
import ConfigSearchFilterCardDateRangePicker from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCardDateRangePicker.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useFilterCard } from './useFilterCard';

defineProps({
  isLoading: Boolean
});
const emit = defineEmits(['searchCompleted']);

const { clearAll, commands, model, performSearch, setDates } = useFilterCard(emit);
</script>

<template>
  <div>
    <Card class="m-2 overflow-hidden">
      <CardHeader class="flex flex-row items-start p-4 bg-muted/50">
        <div class="grid gap-0.5">
          <!-- <CardTitle class="flex items-center gap-2 text-lg group">Device Details</CardTitle> -->
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
              <Select v-model="model.command">
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
              <p class="ml-1 text-xs text-muted-foreground">
                Required
                <span class="text-red-400">*</span>
              </p>
            </div>
            <div class="grid items-center">
              <Input
                v-model="model.search_string"
                id="search_string"
                autocomplete="off"
                placeholder="Search String"
                class="w-full mt-4 mb-1 focus:outline-none focus-visible:ring-0" />
              <p class="ml-1 text-xs text-muted-foreground">
                Required
                <span class="text-red-400">*</span>
              </p>
            </div>

            <div class="grid items-center">
              <Input
                v-model="model.device_name"
                id="device_name"
                autocomplete="off"
                placeholder="Device Name"
                class="w-full mt-4 mb-1 focus:outline-none focus-visible:ring-0" />
              <p class="ml-1 text-xs text-muted-foreground">Enter full or partial device name</p>
            </div>
            <div class="grid items-center">
              <Input
                v-model="model.device_category"
                id="device_category"
                autocomplete="off"
                placeholder="Command Group"
                class="w-full mt-4 mb-1 focus:outline-none focus-visible:ring-0" />
              <p class="ml-1 text-xs text-muted-foreground">Enter full or partial command group name</p>
            </div>

            <div class="flex items-center mt-4 space-x-4">
              <Checkbox
                id="ignore_case"
                v-model:checked="model.ignore_case" />
              <label
                for="ignore_case"
                class="text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Ignore Case
              </label>
            </div>
            <div class="flex items-center mb-4 space-x-4">
              <Checkbox
                id="latest_version_only"
                v-model:checked="model.latest_version_only" />
              <label
                for="latest_version_only"
                class="text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Latest Version Only
              </label>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
              <div class="flex flex-col items-start">
                <Input
                  type="number"
                  id="lines_before"
                  v-model="model.lines_before" />
                <Label
                  for="lines_before"
                  class="mt-2 text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                  Lines Before
                </Label>
              </div>
              <div class="flex flex-col items-start">
                <Input
                  type="number"
                  id="lines_after"
                  v-model="model.lines_after" />
                <Label
                  for="lines_after"
                  class="mt-2 text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                  Lines After
                </Label>
              </div>
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
          @click="performSearch()"
          class="h-6 px-2 py-1 ml-2 text-xs bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          v-if="!isLoading">
          Search
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>
