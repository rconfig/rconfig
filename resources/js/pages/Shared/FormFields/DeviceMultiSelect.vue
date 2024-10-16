<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const emit = defineEmits(['update:modelValue']);
const devices = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedCats = ref([]);

const filteredCategories = computed(() => {
  return devices.value.filter(
    dev => dev.device_name.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedCats.value.some(selectedCat => selectedCat.id === dev.id) // Prevent displaying already selected items
  );
});

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
    selectedCats.value = newValue;
  }
);

onMounted(() => {
  fetchCategories();
});

function selectItem(item) {
  // Add selected item to selectedCats and emit updated list
  selectedCats.value.push(item);
  open.value = false;
  searchTerm.value = '';
  // remove item from filteredCategories
  const itemIndex = filteredCategories.value.findIndex(dev => dev.device_name === item.device_name);
  if (itemIndex !== -1) {
    filteredCategories.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedCats.value);
}

function deleteItem(itemName) {
  // Remove item from selectedCats and emit updated list
  const itemIndex = selectedCats.value.findIndex(dev => dev.device_name === itemName);
  if (itemIndex !== -1) {
    selectedCats.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedCats.value);
}

function fetchCategories() {
  axios.get('/api/devices/?perPage=10000').then(response => {
    devices.value = response.data.data;
  });
}
</script>

<template>
  <Popover>
    <PopoverTrigger class="w-full">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full p-1 pl-2 whitespace-normal border h-fit">
        {{ selectedCats && selectedCats.length === 0 ? 'Select devices' : '' }}
        <span
          v-for="dev in selectedCats"
          :key="dev.id"
          class="relative my-1 group">
          <span
            :class="dev.badgeColor ? dev.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
            class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ dev.device_name }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(dev.device_name)" />
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
          placeholder="Search..."
          class="pl-10 border-none fo5us:outline-none focus:ring-0 text-muted-foreground font-inter" />
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
            v-for="dev in filteredCategories"
            :key="dev.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(dev)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border"
              :class="dev.badgeColor ? dev.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
              <span data-size="20">
                {{ dev.device_name }}
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
