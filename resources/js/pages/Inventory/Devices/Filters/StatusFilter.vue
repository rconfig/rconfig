<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

const emit = defineEmits(['update:modelValue']);
const options = ref([
  { id: 0, name: 'Unreachable' },
  { id: 1, name: 'Reachable' },
  { id: 2, name: 'Unknown' },
  { id: 100, name: 'Disabled' },
  { id: 200, name: 'All' }
]);
const open = ref(false);
const selectedStatus = ref([]);

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  }
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedStatus.value = newValue;
  }
);

function selectItem(item) {
  if (item.id === 200) {
    // If 'All' is selected
    if (selectedStatus.value.length === options.value.length) {
      // If all items including 'All' are already selected, remove all
      selectedStatus.value = [];
    } else {
      // Select all items including 'All'
      selectedStatus.value = [...options.value];
    }
  } else {
    const existingIndex = selectedStatus.value.findIndex(tag => tag.id === item.id);
    if (existingIndex !== -1) {
      // If item exists, remove it
      selectedStatus.value.splice(existingIndex, 1);
      // If 'All' was selected and an item is deselected, remove 'All'
      const allIndex = selectedStatus.value.findIndex(tag => tag.id === 200);
      if (allIndex !== -1) {
        selectedStatus.value.splice(allIndex, 1);
      }
    } else {
      // If item does not exist, add it
      selectedStatus.value.push(item);
    }
  }
  open.value = false;
  emit('update:modelValue', selectedStatus.value);
}
</script>

<template>
  <Popover>
    <PopoverTrigger>
      <Button
        variant="ghost"
        class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400">
        <Icon
          icon="material-symbols-light:signal-wifi-statusbar-not-connected-rounded"
          class="mr-2" />

        <template v-if="selectedStatus && selectedStatus.length === 0">Filter status</template>
        <template v-else>
          <span
            class="text-sm font-light"
            v-if="selectedStatus.length > 0">
            Status equals
            <strong class="text-sm font-semibold">{{ selectedStatus.length }} selected</strong>
          </span>
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent
      side="bottom"
      align="start"
      class="p-0 w-44">
      <ScrollArea class="h-44">
        <div class="py-1">
          <div
            v-for="option in options"
            :key="option.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(option)">
            <input
              type="checkbox"
              :checked="selectedStatus.some(cat => cat.id === option.id)"
              class="mr-2" />
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
              <span data-size="20">
                {{ option.name }}
              </span>
            </span>
          </div>
        </div>
      </ScrollArea>
      <!-- <Separator />

      <div class="flex justify-between gap-4 p-2 border-5">
        <Button
          variant="ghost"
          class="h-6 py-1 text-sm">
          <span>Cancel</span>
        </Button>
        <Button
          variant="ghost"
          class="h-6 px-4 text-sm bg-blue-600 hover:bg-blue-500">
          <span>Apply</span>
        </Button>
      </div> -->
    </PopoverContent>
  </Popover>
</template>
