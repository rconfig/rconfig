<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref, watch, computed } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import CommandsIcon from '@/pages/Shared/Icon/CommandsIcon.vue';

const emit = defineEmits(['update:modelValue']);
const options = ref([]);
const open = ref(false);
const selectedCommand = ref([]);
const searchTerm = ref('');
const allSelected = ref(false);

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  },
  deviceId: {
    type: Number,
    default: 0
  }
});

onMounted(() => {
  fetchCommand();
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedCommand.value = newValue;
    allSelected.value = selectedCommand.value.length === options.value.length;
  }
);

function selectItem(item) {
  if (item.id === 9999999) {
    // If 'All' is selected, toggle selection state
    if (allSelected.value) {
      // If all items are already selected, remove all
      selectedCommand.value = [];
      allSelected.value = false;
    } else {
      // Select all items
      selectedCommand.value = [...options.value];
      allSelected.value = true;
    }
  } else {
    const existingIndex = selectedCommand.value.findIndex(option => option.command === item.command);
    if (existingIndex !== -1) {
      // If item exists, remove it
      selectedCommand.value.splice(existingIndex, 1);
      allSelected.value = false;
    } else {
      // If item does not exist, add it
      selectedCommand.value.push(item);
      if (selectedCommand.value.length === options.value.length) {
        allSelected.value = true;
      }
    }
  }
  open.value = false;
  emit('update:modelValue', selectedCommand.value);
}

function fetchCommand() {
  axios.get(`/api/configs/distinct-commands/${props.deviceId}/?perPage=10000`).then(response => {
    options.value = response.data.data;
  });
}

const filteredCommand = computed(() => {
  return options.value.filter(
    option => option.command.toLowerCase().includes(searchTerm.value.toLowerCase()) // Prevent displaying already selected items
  );
});
</script>

<template>
  <Popover>
    <PopoverTrigger>
      <Button
        variant="ghost"
        class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400">
        <CommandsIcon class="mr-2" />

        <template v-if="selectedCommand && selectedCommand.length === 0">Command</template>
        <template v-else>
          <span
            class="text-sm font-light"
            v-if="selectedCommand.length > 0">
            Command
            <strong class="text-sm font-semibold">{{ selectedCommand.length }} selected</strong>
          </span>
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent
      side="bottom"
      align="start"
      class="w-64 p-0">
      <div class="relative items-center w-full">
        <Input
          id="search"
          type="text"
          v-model="searchTerm"
          placeholder="Search..."
          class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
          <Icon
            icon="weui:search-outlined"
            class="size-6 text-muted-foreground" />
        </span>
      </div>
      <Separator />
      <ScrollArea class="h-44">
        <div class="py-1">
          <div
            v-for="option in filteredCommand"
            :key="option.command"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(option)">
            <input
              type="checkbox"
              :checked="selectedCommand.some(option => option.command === option.command)"
              class="mr-2" />
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
              <span data-size="20">
                {{ option.command }}
              </span>
            </span>
          </div>
        </div>
      </ScrollArea>
      <Separator />

      <div class="p-1 border-5">
        <Button
          variant="ghost"
          class="justify-start w-full p-1"
          @click="selectItem({ id: 9999999 })">
          <Icon
            :icon="allSelected ? 'fluent:select-all-on-16-filled' : 'fluent:select-all-on-16-regular'"
            class="w-4 h-4 mr-2 text-muted-foreground" />

          <span>{{ allSelected ? 'Deselect all' : 'Select all' }}</span>
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
