<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

const emit = defineEmits(['update:modelValue']);
const vendors = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedVendors = ref([]);

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
  return vendors.value.filter(
    vendor => vendor.vendorName.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedVendors.value.some(selectedCat => selectedCat.id === vendor.id) // Prevent displaying already selected items
  );
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedVendors.value = newValue;
  }
);

onMounted(() => {
  fetchVendors();

  if (props.modelValue && props.modelValue.length > 0) {
    selectedVendors.value = props.modelValue;
  }
});

function selectItem(item) {
  if (props.singleSelect) {
    // If singleSelect is true, replace the selected item
    selectedVendors.value = [item];
  } else {
    // Add selected item to selectedVendors
    selectedVendors.value.push(item);
  }
  open.value = false;
  searchTerm.value = '';
  emit('update:modelValue', selectedVendors.value);
}

function deleteItem(itemName) {
  // Remove item from selectedVendors and emit updated list
  const itemIndex = selectedVendors.value.findIndex(vendor => vendor.vendorName === itemName);
  if (itemIndex !== -1) {
    selectedVendors.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedVendors.value);
}

function fetchVendors() {
  axios.get('/api/vendors/?perPage=10000').then(response => {
    vendors.value = response.data.data;
  });
}
</script>

<template>
  <Popover>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit"
        :class="selectedVendors.length === 0 ? 'text-muted-foreground' : ' '"
        :style="selectedVendors.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
        <!-- Padding is 0.45rem to match Inputs and adjustment when adding vendors -->
        {{ selectedVendors && selectedVendors.length === 0 ? 'Select vendors' : '' }}
        <span
          v-for="vendor in selectedVendors"
          :key="vendor.id"
          class="relative my-1 group">
          <span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ vendor.vendorName }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(vendor.vendorName)" />
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
            v-for="vendor in filteredVendors"
            :key="vendor.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(vendor)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
              <span data-size="20">
                {{ vendor.vendorName }}
              </span>
            </span>
          </div>
        </div>
      </ScrollArea>

      <Separator />

      <div class="p-1 border-5">
        <Button
          variant="ghost"
          class="justify-start w-full p-1">
          <Icon
            icon="octicon:plus-16"
            class="w-3 h-3 mt-1 mr-2 text-muted-foreground" />
          <span>Create new record</span>
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
