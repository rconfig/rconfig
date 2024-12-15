<script setup>
import { ref } from 'vue';
import Step5TaskVerifyProgress from './Step5TaskVerifyProgress.vue';
import cronstrue from 'cronstrue';

const props = defineProps({
  model: Object
});

const emit = defineEmits(['itemChecked', 'checkSuccess']);

const hoveringCategories = ref(false);
const hoveringDevices = ref(false);
const hoveringTags = ref(false);
const defaultSlice = ref(4);
const cronToHuman = ref('');
const validationErrors = ref([]);
const showAll = ref(false);
cronToHuman.value = cronstrue.toString(props.model.task_cron.join(' '));

function toggleDefaultSlice(length) {
  if (length <= 4) return; // No need to toggle if less than 4 (default slice, but cannot use defaultSlice.value for this check, as it gets updated)

  if (defaultSlice.value === 4) {
    defaultSlice.value = length;
  } else {
    defaultSlice.value = 4;
  }
  showAll.value = !showAll.value;
}

function checkSuccess() {
  emit('checkSuccess');
}

function setErrors(errors) {
  validationErrors.value = errors;
}
</script>

<template>
  <div>
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Step 5 - Finalize Task</h3>
    <!-- <pre> {{ model }}</pre> -->

    <div class="card">
      <Step5TaskVerifyProgress
        @errors="setErrors($event)"
        :model="model"
        @checkSuccess="checkSuccess()" />
      <div
        class="grid gap-2"
        v-if="validationErrors.data">
        <div class="grid items-center grid-cols-3 gap-4">
          <Label class="text-muted-foreground">Task Errors:</Label>
          <span class="col-span-2 font-light text-red-500">{{ validationErrors.data.message }}</span>
        </div>
      </div>

      <h2 class="my-4">Task Details</h2>

      <div class="grid gap-2">
        <div class="grid gap-2">
          <div class="grid items-center grid-cols-3 gap-4">
            <Label class="text-muted-foreground">Task Name:</Label>
            <span class="col-span-2 font-light">{{ model.task_name }}</span>
          </div>
        </div>
        <div class="grid gap-2">
          <div class="grid items-center grid-cols-3 gap-4">
            <Label class="text-muted-foreground">Description:</Label>
            <span class="col-span-2 font-light">{{ model.task_desc || 'No description provided' }}</span>
          </div>
        </div>
        <div class="grid gap-2">
          <div class="grid items-center grid-cols-3 gap-4">
            <Label class="text-muted-foreground">Command:</Label>
            <span class="col-span-2 font-light">{{ model.task_command }}</span>
          </div>
        </div>
        <!-- CATEGORIES -->

        <div
          class="grid gap-2"
          v-if="model.task_categories === 1">
          <div class="grid items-center grid-cols-3 gap-4 group">
            <Label class="text-muted-foreground">Categories:</Label>
            <div
              class="flex flex-wrap items-start justify-start w-full col-span-2 p-1 whitespace-normal h-fit group-hover:bg-rcgray-800 group-hover:cursor-pointer group-hover:rounded-lg"
              @click="toggleDefaultSlice(model.category.length)"
              @mouseover="hoveringCategories = true"
              @mouseleave="hoveringCategories = false">
              <span
                v-for="(dev, index) in model.category.slice(0, defaultSlice)"
                :key="dev.id">
                <span
                  :class="dev.badgeColor ? dev.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
                  class="items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border ml-1">
                  {{ dev.categoryName }}
                </span>
                <span
                  :class="!hoveringCategories ? 'border bg-gray-600 border-gray-500 text-gray-200' : ' text-gray-400'"
                  class="items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="model.category.length > defaultSlice && defaultSlice === 4 && index === 3">
                  <span v-if="hoveringCategories">Show all</span>
                  <span v-else>+ {{ model.category.length - 4 }}</span>
                </span>
                <span
                  class="text-gray-400 items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="defaultSlice === index + 1 && showAll">
                  <span>Show less</span>
                </span>
              </span>
            </div>
          </div>
        </div>

        <!-- CATEGORIES -->

        <!-- DEVICES -->
        <div
          class="grid gap-2"
          v-if="model.task_devices === 1">
          <div class="grid items-center grid-cols-3 gap-4 group">
            <Label class="text-muted-foreground">Devices:</Label>
            <div
              class="flex flex-wrap items-start justify-start w-full col-span-2 p-1 whitespace-normal h-fit group-hover:bg-rcgray-800 group-hover:cursor-pointer group-hover:rounded-lg"
              @click="toggleDefaultSlice(model.device.length)"
              @mouseover="hoveringDevices = true"
              @mouseleave="hoveringDevices = false">
              <span
                v-for="(dev, index) in model.device.slice(0, defaultSlice)"
                :key="dev.id">
                <span
                  :class="dev.badgeColor ? dev.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
                  class="items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border ml-1">
                  {{ dev.device_name }}
                </span>
                <span
                  :class="!hoveringDevices ? 'border bg-gray-600 border-gray-500 text-gray-200' : ' text-gray-400'"
                  class="items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="model.device.length > defaultSlice && defaultSlice === 4 && index === 3">
                  <span v-if="hoveringDevices">Show all</span>
                  <span v-else>+ {{ model.device.length - 4 }}</span>
                </span>
                <span
                  class="text-gray-400 items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="defaultSlice === index + 1 && showAll">
                  <span>Show less</span>
                </span>
              </span>
            </div>
          </div>
        </div>
        <!-- DEVICES -->

        <!-- TAGS -->

        <div
          class="grid gap-2"
          v-if="model.task_tags === 1">
          <div class="grid items-center grid-cols-3 gap-4 group">
            <Label class="text-muted-foreground">Tags:</Label>
            <div
              class="flex flex-wrap items-start justify-start w-full col-span-2 p-1 whitespace-normal h-fit group-hover:bg-rcgray-800 group-hover:cursor-pointer group-hover:rounded-lg"
              @click="toggleDefaultSlice(model.tag.length)"
              @mouseover="hoveringTags = true"
              @mouseleave="hoveringTags = false">
              <span
                v-for="(dev, index) in model.tag.slice(0, defaultSlice)"
                :key="dev.id">
                <span
                  :class="dev.badgeColor ? dev.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
                  class="items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border ml-1">
                  {{ dev.tagname }}
                </span>
                <span
                  :class="!hoveringTags ? 'border bg-gray-600 border-gray-500 text-gray-200' : ' text-gray-400'"
                  class="items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="model.tag.length > defaultSlice && defaultSlice === 4 && index === 3">
                  <span v-if="hoveringTags">Show all</span>
                  <span v-else>+ {{ model.tag.length - 4 }}</span>
                </span>
                <span
                  class="text-gray-400 items-center text-xs font-normal px-2.5 py-0.5 rounded-xl ml-1"
                  v-if="defaultSlice === index + 1 && showAll">
                  <span>Show less</span>
                </span>
              </span>
            </div>
          </div>
        </div>

        <!-- TAGS -->

        <div class="grid gap-2">
          <div class="grid items-center grid-cols-3 gap-4">
            <Label class="text-muted-foreground">Task Schedule:</Label>
            <span class="col-span-2 font-light">{{ cronToHuman }}</span>
          </div>
        </div>
        <div class="grid gap-2">
          <div class="grid items-center grid-cols-3 gap-4">
            <Label class="text-muted-foreground">Task Reporting:</Label>
            <div class="flex flex-col col-span-2 gap-2 font-light">
              <span class="flex items-center">
                <AlertIcon class="flex-shrink-0 h-4 mr-2 text-blue-600" />
                Task notification email will be sent after each run
              </span>
              <span
                class="flex items-center"
                v-if="model.download_report_notify">
                <AlertIcon class="flex-shrink-0 h-4 mr-2 text-blue-600" />
                A device download/ snippet failure report will be sent after task completes.
              </span>
              <span
                class="flex items-center"
                v-if="model.verbose_download_report_notify">
                <AlertIcon class="flex-shrink-0 h-4 mr-2 text-blue-600" />
                A verbose device download/ snippet failure report will not be sent after task completes.
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
