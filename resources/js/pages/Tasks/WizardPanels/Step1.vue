<script setup>
import { ref, onMounted } from 'vue';

const selectedValue = ref(null);

const props = defineProps({
  model: Object
});

const handleCheck = value => {
  selectedValue.value = value;
  props.model.task_command = value;
  setTaskType(value);
  // remove these properties when the task type is changed
  delete props.model.category;
  delete props.model.tag;
  delete props.model.device;
};

function setTaskType(value) {
  switch (value) {
    case 'rconfig:download-device':
      props.model.task_devices = 1;
      props.model.task_tags = 0;
      props.model.task_categories = 0;
      break;
    case 'rconfig:download-category':
      props.model.task_categories = 1;
      props.model.task_devices = 0;
      props.model.task_tags = 0;
      break;
    case 'rconfig:download-tag':
      props.model.task_tags = 1;
      props.model.task_devices = 0;
      props.model.task_categories = 0;
      break;
  }
}

onMounted(() => {
  if (props.model.task_command) {
    selectedValue.value = props.model.task_command;
  }
});
</script>

<template>
  <div>
    <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Select a task type</h3>
    <ul class="grid w-full gap-4 md:grid-cols-1">
      <li class="text-sm">
        <input
          type="radio"
          id="devices"
          name="hosting"
          value="devices"
          class="hidden peer"
          @change="handleCheck('rconfig:download-device')" />
        <label
          for="devices"
          class="inline-flex items-center justify-between w-full p-2 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-rcgray-900 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-rcgray-800 dark:hover:bg-gray-700">
          <div class="block">
            <div class="flex items-center w-full font-semibold">
              <Icon icon="fluent-color:org-16" />
              <span class="ml-1">Devices</span>
            </div>
            <div class="w-full">Select one or many devices to backup</div>
          </div>
          <Icon
            v-if="selectedValue === 'rconfig:download-device'"
            icon="solar:check-circle-broken"
            class="w-8 h-8 text-green-500" />
          <Icon
            v-else
            icon="uil:arrow-right"
            class="w-8 h-8 text-blue-500 hover:animate-ping" />
        </label>
      </li>
      <li class="text-sm">
        <input
          type="radio"
          id="categories"
          name="hosting"
          value="categories"
          class="hidden peer"
          @change="handleCheck('rconfig:download-category')" />
        <label
          for="categories"
          class="inline-flex items-center justify-between w-full p-2 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-rcgray-900 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-rcgray-800 dark:hover:bg-gray-700">
          <div class="block">
            <div class="flex items-center w-full font-semibold">
              <Icon icon="fluent-color:search-visual-24" />
              <span class="ml-1">Command Groups</span>
            </div>
            <div class="w-full">Select one or many command groups to backup</div>
          </div>
          <Icon
            v-if="selectedValue === 'rconfig:download-category'"
            icon="solar:check-circle-broken"
            class="w-8 h-8 text-green-500" />
          <Icon
            v-else
            icon="uil:arrow-right"
            class="w-8 h-8 text-blue-500 hover:animate-ping" />
        </label>
      </li>
      <li class="text-sm">
        <input
          type="radio"
          id="tags"
          name="hosting"
          value="tags"
          class="hidden peer"
          @change="handleCheck('rconfig:download-tag')" />
        <label
          for="tags"
          class="inline-flex items-center justify-between w-full p-2 text-gray-500 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-rcgray-900 dark:peer-checked:text-blue-500 peer-checked:border-blue-600 peer-checked:text-blue-600 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:bg-rcgray-800 dark:hover:bg-gray-700">
          <div class="block">
            <div class="flex items-center w-full font-semibold">
              <Icon icon="fluent-emoji:keycap-hashtag" />
              <span class="ml-1">Tags</span>
            </div>
            <div class="w-full">Select one or many tags to backup</div>
          </div>
          <Icon
            v-if="selectedValue === 'rconfig:download-tag'"
            icon="solar:check-circle-broken"
            class="w-8 h-8 text-green-500" />
          <Icon
            v-else
            icon="uil:arrow-right"
            class="w-8 h-8 text-blue-500 hover:animate-ping" />
        </label>
      </li>
    </ul>
  </div>
</template>
