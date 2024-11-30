<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import ConfigSearchFilterCardDateRangePicker from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCardDateRangePicker.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';

defineProps({
  isLoading: Boolean
});

const emit = defineEmits(['searchCompleted']);

const model = reactive({
  device_name: '',
  command: '',
  device_category: '',
  search_string: '',
  lines_before: 5,
  lines_after: 5,
  latest_version_only: ref(true),
  ignore_case: ref(true),
  start_date: '',
  end_date: ''
});
const daterange = ref(null);
const commands = ref([]);
const results = reactive({});

onMounted(() => {
  // window.addEventListener('wheel', handleScroll, { passive: true });
  getDistinctCommands();
});

function getDistinctCommands() {
  axios
    .get('/api/configs/distinct-commands/0')
    .then(response => {
      commands.value = response.data.data;
    })
    .catch(error => {
      createNotification({
        type: 'danger',
        message: error.response.data.message
      });
    });
}

function clearAll() {
  model.device_name = '';
  model.command = '';
  model.device_category = '';
  model.search_string = '';
  model.lines_before = 5;
  model.lines_after = 5;
  model.latest_version_only = ref(true);
  model.ignore_case = ref(true);
  model.start_date = '';
  model.end_date = '';
  Object.keys(results).forEach(key => delete results[key]);
  emit('searchCompleted', model);
}

function setDates(dateRange) {
  if (dateRange.start && dateRange.end) {
    // Convert the start and end dates to the desired format (YYYY-MM-DD)
    const startDate = `${dateRange.start.year}-${String(dateRange.start.month).padStart(2, '0')}-${String(dateRange.start.day).padStart(2, '0')}`;
    const endDate = `${dateRange.end.year}-${String(dateRange.end.month).padStart(2, '0')}-${String(dateRange.end.day).padStart(2, '0')}`;

    // Update the model with the transformed dates
    model.start_date = startDate;
    model.end_date = endDate;
  } else {
    // If no date range is provided, reset the model
    model.start_date = '';
    model.end_date = '';
  }

  // Optional: Update the Vue state or other reactive properties
  daterange.value = dateRange;
}

function search() {
  emit('searchCompleted', model);
}
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

            <div class="flex items-center mb-4 space-x-4">
              <Input
                type="number"
                id="lines_before"
                v-model="model.lines_before" />
              <Input
                type="number"
                id="lines_after"
                v-model="model.lines_after" />
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
          @click="search()"
          class="h-6 px-2 py-1 ml-2 text-xs bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          v-if="!isLoading">
          Search
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>
