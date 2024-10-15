<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { ComboboxAnchor, ComboboxContent, ComboboxInput, ComboboxPortal, ComboboxRoot } from 'radix-vue';
import { CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
// NOTES:
// - This component is used in the CommandAddEditDialog.vue component as reference
// - This component is used to select multiple tags
// - fetchTags is called onMounted to fetch all tags from the API
// - selectItem is called when a tag is selected and emitted
// - the parent must implement the 'update:modelValue = $event' to receive the selected tags as a flat array of tag ids
// - the parent must pass the inbound tags as an array of full tag objects
// - The 'inboundTags' prop is used to pass the inbound tags and is flatted to an array of tag ids within the component

const emit = defineEmits(['update:modelValue']);
const tags = [];
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedTags = ref([]);

const filteredFrameworks = computed(() => tags.filter(i => !modelValue.value.includes(i.label)));

const props = defineProps({
  inboundTags: {
    type: Array, // array of full tag objects
    default: []
  }
});

onMounted(() => {
  fetchTags();
});

// watch inboundTags
watch(
  () => props.inboundTags,
  newVal => {
    if (newVal.length > 0) {
      selectedTags.value = newVal.map(item => item.id);
      modelValue.value = newVal.map(item => item.tagname);
    }
  }
);

function selectItem(item) {
  // item is the whole object label and value
  modelValue.value.push(item.label);
  open.value = false;
  searchTerm.value = '';
  selectedTags.value.push(item.value);
  emit('update:modelValue', selectedTags.value);
}

function deleteItem(item) {
  // item is the label of the tag
  modelValue.value = modelValue.value.filter(i => i !== item);
  selectedTags.value = selectedTags.value.filter(i => i !== tags.find(c => c.label === item).value);
  emit('update:modelValue', selectedTags);
}

function fetchTags() {
  axios.get('/api/tags/?perPage=10000').then(response => {
    const fetchedTags = response.data.data.map(tag => ({
      value: tag.id,
      label: tag.tagname
    }));
    tags.push(...fetchedTags);
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
          placeholder="Select a Tag..."
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
          class="overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 w-[--radix-popper-anchor-width] rounded-md mt-2 border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2">
          <CommandEmpty>No tags found</CommandEmpty>
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
