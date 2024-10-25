<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

const emit = defineEmits(['update:modelValue']);
const tags = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedTags = ref([]);

const filteredCategories = computed(() => {
  return tags.value.filter(
    tag => tag.tagname.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedTags.value.some(selectedCat => selectedCat.id === tag.id) // Prevent displaying already selected items
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
    selectedTags.value = newValue;
  }
);

onMounted(() => {
  fetchCategories();

  if (props.modelValue && props.modelValue.length > 0) {
    selectedTags.value.push(...props.modelValue);
  }
});

function selectItem(item) {
  // Add selected item to selectedTags and emit updated list
  selectedTags.value.push(item);
  open.value = false;
  searchTerm.value = '';
  // remove item from filteredCategories
  const itemIndex = filteredCategories.value.findIndex(tag => tag.tagname === item.tagname);
  if (itemIndex !== -1) {
    filteredCategories.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedTags.value);
}

function deleteItem(itemName) {
  // Remove item from selectedTags and emit updated list
  const itemIndex = selectedTags.value.findIndex(tag => tag.tagname === itemName);
  if (itemIndex !== -1) {
    selectedTags.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedTags.value);
}

function fetchCategories() {
  axios.get('/api/tags/?perPage=10000').then(response => {
    tags.value = response.data.data;
  });
}
</script>

<template>
  <Popover>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit"
        :class="selectedTags.length === 0 ? 'text-muted-foreground' : ' '"
        :style="selectedTags.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
        <!-- Padding is 0.45rem to match Inputs and adjustment when adding tags -->

        {{ selectedTags && selectedTags.length === 0 ? 'Select tags' : '' }}
        <span
          v-for="tag in selectedTags"
          :key="tag.id"
          class="relative my-1 group">
          <span
            :class="tag.badgeColor ? tag.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'"
            class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ tag.tagname }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(tag.tagname)" />
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
            v-for="tag in filteredCategories"
            :key="tag.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(tag)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border"
              :class="tag.badgeColor ? tag.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'">
              <span data-size="20">
                {{ tag.tagname }}
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
