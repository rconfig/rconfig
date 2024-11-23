<script setup lang="ts">
import CommandGroupAddEditDialog from '@/pages/Inventory/CommandGroups/CommandGroupAddEditDialog.vue';
import axios from 'axios';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { computed, onMounted, ref, watch } from 'vue';
import { useCommandGroups } from '@/pages/Inventory/CommandGroups/useCommandGroups';

const emit = defineEmits(['update:modelValue']);

const { newCommandGroupsModalKey, handleSave, viewEditDialog } = useCommandGroups();

const categories = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedCats = ref([]);

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

const filteredCategories = computed(() => {
  return categories.value.filter(
    cat => cat.categoryName.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedCats.value.some(selectedCat => selectedCat.id === cat.id) // Prevent displaying already selected items
  );
});

onMounted(() => {
  fetchCategories();

  if (props.modelValue && props.modelValue.length > 0) {
    selectedCats.value = props.modelValue;
  }
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedCats.value = newValue;
  }
);

watch(newCommandGroupsModalKey, () => {
  // if the add new category dialog is closed, fetch categories again
  fetchCategories();
});

function selectItem(item) {
  if (props.singleSelect) {
    // If singleSelect is true, replace the selected item
    selectedCats.value = [item];
  } else {
    // Add selected item to selectedCats
    selectedCats.value.push(item);
  }
  open.value = false;
  searchTerm.value = '';
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
        class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit"
        :class="selectedCats.length === 0 ? 'text-muted-foreground' : ' '"
        :style="selectedCats.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
        <!-- Padding is 0.45rem to match Inputs and adjustment when adding cats -->
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
          @click="viewEditDialog(0)"
          class="justify-start w-full p-1">
          <Icon
            icon="octicon:plus-16"
            class="w-3 h-3 mt-1 mr-2 text-muted-foreground" />
          <span>Create new record</span>
        </Button>
      </div>
    </PopoverContent>

    <CommandGroupAddEditDialog
      @save="handleSave()"
      :key="newCommandGroupsModalKey"
      :editId="0" />
  </Popover>
</template>
