<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { ComboboxAnchor, ComboboxContent, ComboboxInput, ComboboxPortal, ComboboxRoot } from 'radix-vue';
import { CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
// NOTES:
// - This component is used in the CommandAddEditDialog.vue component as reference
// - This component is used to select multiple categories
// - fetchCategories is called onMounted to fetch all categories from the API
// - selectItem is called when a category is selected and emitted
// - the parent must implement the 'update:modelValue = $event' to receive the selected categories as a flat array of category ids
// - the parent must pass the inbound categories as an array of full category objects
// - The 'inboundCats' prop is used to pass the inbound categories and is flatted to an array of category ids within the component

const emit = defineEmits(['update:modelValue']);
const categories = [];
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedCats = ref([]);

const filteredFrameworks = computed(() => categories.filter(i => !modelValue.value.includes(i.label)));

const props = defineProps({
  inboundCats: {
    type: Array, // array of full category objects
    default: []
  }
});

onMounted(() => {
  fetchCategories();
});

// watch inboundCats
watch(
  () => props.inboundCats,
  newVal => {
    if (newVal.length > 0) {
      selectedCats.value = newVal.map(item => item.id);
      modelValue.value = newVal.map(item => item.categoryName);
    }
  }
);

function selectItem(item) {
  // item is the whole object label and value
  modelValue.value.push(item.label);
  open.value = false;
  searchTerm.value = '';
  selectedCats.value.push(item.value);
  emit('update:modelValue', selectedCats.value);
  console.log(selectedCats.value);
}

function deleteItem(item) {
  // item is the label of the category
  modelValue.value = modelValue.value.filter(i => i !== item);
  selectedCats.value = selectedCats.value.filter(i => i !== categories.find(c => c.label === item).value);
  emit('update:modelValue', selectedCats);
}

function fetchCategories() {
  axios.get('/api/categories/?perPage=10000').then(response => {
    const fetchedCategories = response.data.data.map(category => ({
      value: category.id,
      label: category.categoryName
    }));
    categories.push(...fetchedCategories);
  });
}
</script>

<template>
  <TagsInput class="gap-0 px-0 min-w-96">
    <div class="flex flex-wrap items-center gap-2 px-3">
      <TagsInputItem
        v-for="item in modelValue"
        :key="item"
        :value="item">
        <TagsInputItemText></TagsInputItemText>
        <TagsInputItemDelete @click.prevent="deleteItem(item)" />
      </TagsInputItem>
    </div>
    <ComboboxRoot
      v-model="modelValue"
      v-model:open="open"
      v-model:searchTerm="searchTerm"
      class="w-full">
      <ComboboxAnchor as-child>
        <ComboboxInput
          placeholder="Command Group..."
          as-child>
          <TagsInputInput
            class="w-full px-3"
            :class="modelValue.length > 0 ? 'mt-2' : ''"
            @keydown.enter.prevent />
        </ComboboxInput>
      </ComboboxAnchor>

      <ComboboxContent>
        <CommandList
          position="popper"
          class="w-[--radix-popper-anchor-width] rounded-md mt-2 border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2">
          <CommandEmpty>No categories found</CommandEmpty>
          <CommandGroup>
            <CommandItem
              v-for="item in filteredFrameworks"
              :key="item.value"
              :value="item.label"
              @select.prevent="selectItem(item)">
              {{ item.label }}
            </CommandItem>
          </CommandGroup>
        </CommandList>
      </ComboboxContent>
    </ComboboxRoot>
  </TagsInput>
</template>
