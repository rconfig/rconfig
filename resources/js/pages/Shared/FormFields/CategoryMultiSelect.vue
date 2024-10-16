<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const emit = defineEmits(['update:modelValue']);
const categories = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedCats = ref([]);

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  }
});

const filteredCategories = computed(() => {
  return categories.value.filter(
    cat => cat.categoryName.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedCats.value.some(selectedCat => selectedCat.id === cat.id) // Prevent displaying already selected items
  );
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

  if (props.modelValue && props.modelValue.length > 0) {
    selectedCats.value = props.modelValue;
  }
});

function selectItem(item) {
  // Add selected item to selectedCats and emit updated list
  selectedCats.value.push(item);
  open.value = false;
  searchTerm.value = '';
  // remove item from filteredCategories
  const itemIndex = filteredCategories.value.findIndex(cat => cat.categoryName === item.categoryName);
  if (itemIndex !== -1) {
    filteredCategories.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedCats.value);
}

function deleteItem(itemName) {
  // Remove item from selectedCats and emit updated list
  const itemIndex = selectedCats.value.findIndex(cat => cat.categoryName === itemName);
  if (itemIndex !== -1) {
    selectedCats.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedCats.value);
}

function fetchCategories() {
  axios.get('/api/categories/?perPage=10000').then(response => {
    categories.value = response.data.data;
  });
}
</script>

<template>
  <!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
  <Popover>
    <div class="hidden text-yellow-200 text-teal-100 bg-yellow-700 bg-teal-700 border-yellow-500 border-teal-500 bg-stone-700 text-stone-200 border-stone-500 bg-lime-700 text-lime-200 border-lime-500 bg-sky-700 text-sky-100 border-sky-500 bg-violet-700 text-violet-200 border-violet-500 bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500"></div>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full p-1 pl-2 whitespace-normal border h-fit">
        {{ selectedCats && selectedCats.length === 0 ? 'Select categories' : '' }}
        <span
          v-for="cat in selectedCats"
          :key="cat.id"
          class="relative my-1 group">
          <span
            :class="cat.badgeColor ? cat.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
            class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ cat.categoryName }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(cat.categoryName)" />
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
            v-for="cat in filteredCategories"
            :key="cat.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(cat)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border"
              :class="cat.badgeColor ? cat.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
              <span data-size="20">
                {{ cat.categoryName }}
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
