<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import DeviceModelAddDialog from '@/pages/Shared/FormFields/DeviceModelAddDialog.vue';

const emit = defineEmits(['update:modelValue']);
const DeviceModels = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedDeviceModels = ref([]);

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  },
  singleSelect: {
    type: Boolean,
    default: false
  }
});

const filteredVendors = computed(() => {
  return DeviceModels.value.filter(
    deviceModel => deviceModel.name.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedDeviceModels.value.some(selectedCat => selectedCat.id === deviceModel.id) // Prevent displaying already selected items
  );
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedDeviceModels.value = newValue;
  }
);

onMounted(() => {
  fetchDeviceModels();

  if (props.modelValue && props.modelValue.length > 0) {
    selectedDeviceModels.value = props.modelValue;
  }
});

function selectItem(item) {
  if (props.singleSelect) {
    // If singleSelect is true, replace the selected item
    selectedDeviceModels.value = [item];
  } else {
    // Add selected item to selectedDeviceModels
    selectedDeviceModels.value.push(item);
  }
  open.value = false;
  searchTerm.value = '';
  emit('update:modelValue', selectedDeviceModels.value);
}

function deleteItem(itemName) {
  // Remove item from selectedDeviceModels and emit updated list
  const itemIndex = selectedDeviceModels.value.findIndex(deviceModel => deviceModel.name === itemName);
  if (itemIndex !== -1) {
    selectedDeviceModels.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedDeviceModels.value);
}

function fetchDeviceModels() {
  axios.get('/api/device-models/?perPage=10000').then(response => {
    DeviceModels.value = response.data.data;
  });
}

function handleSave() {
  fetchDeviceModels();
}
</script>

<template>
  <Popover>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit"
        :class="selectedDeviceModels.length === 0 ? 'text-muted-foreground' : ' '"
        :style="selectedDeviceModels.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
        <!-- Padding is 0.45rem to match Inputs and adjustment when adding DeviceModels -->
        {{ selectedDeviceModels && selectedDeviceModels.length === 0 ? 'Select device model' : '' }}
        <span
          v-for="deviceModel in selectedDeviceModels"
          :key="deviceModel.id"
          class="relative my-1 group">
          <span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ deviceModel.name }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(deviceModel.name)" />
          </span>
        </span>
      </Button>
    </PopoverTrigger>
    <PopoverContent
      side="bottom"
      align="start"
      class="col-span-3 p-0">
      <div class="relative items-center w-full">
        <Input
          id="search"
          type="text"
          v-model="searchTerm"
          autocomplete="off"
          placeholder="Search..."
          class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
        <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
          <Icon
            icon="weui:search-outlined"
            class="size-6 text-muted-foreground" />
        </span>
      </div>
      <Separator />

      <ScrollArea class="h-64">
        <div class="py-1">
          <div
            v-for="deviceModel in filteredVendors"
            :key="deviceModel.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(deviceModel)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
              <span data-size="20">
                {{ deviceModel.name }}
              </span>
            </span>
          </div>
        </div>
      </ScrollArea>

      <Separator />

      <div class="p-1 border-5">
        <DeviceModelAddDialog @save="handleSave()" />
      </div>
    </PopoverContent>
  </Popover>
</template>
