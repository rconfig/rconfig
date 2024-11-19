<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

const emit = defineEmits(['update:modelValue']);
const templates = ref([]);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');
const selectedTemplates = ref([]);

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
  return templates.value.filter(
    template => template.templateName.toLowerCase().includes(searchTerm.value.toLowerCase()) && !selectedTemplates.value.some(selectedTemplate => selectedTemplate.id === template.id) // Prevent displaying already selected items
  );
});

// Watch for changes to the prop and update internalModel
watch(
  () => props.modelValue,
  newValue => {
    selectedTemplates.value = newValue;
  }
);

onMounted(() => {
  fetchVendors();

  if (props.modelValue[0] && props.modelValue[0].length > 0) {
    console.log('props.modelValue', props.modelValue);

    selectedTemplates.value = props.modelValue;
  }
});

function selectItem(item) {
  if (props.singleSelect) {
    // If singleSelect is true, replace the selected item
    selectedTemplates.value = [item];
  } else {
    // Add selected item to selectedTemplates
    selectedTemplates.value.push(item);
  }
  open.value = false;
  searchTerm.value = '';
  emit('update:modelValue', selectedTemplates.value);
}

function deleteItem(itemName) {
  // Remove item from selectedTemplates and emit updated list
  const itemIndex = selectedTemplates.value.findIndex(template => template.templateName === itemName);
  if (itemIndex !== -1) {
    selectedTemplates.value.splice(itemIndex, 1);
  }
  emit('update:modelValue', selectedTemplates.value);
}

function fetchVendors() {
  axios.get('/api/templates/?perPage=10000').then(response => {
    templates.value = response.data.data;
  });
}
</script>

<template>
  <!-- DIV FOR RENDERING THE BADGE COLOR CLASSES -->
  <Popover>
    <PopoverTrigger class="col-span-3">
      <Button
        variant="ghost"
        class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit"
        :class="selectedTemplates.length === 0 ? 'text-muted-foreground' : ' '"
        :style="selectedTemplates.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
        <!-- Padding is 0.45rem to match Inputs and adjustment when adding templates -->
        {{ selectedTemplates && selectedTemplates.length === 0 ? 'Select templates' : '' }}
        <span
          v-for="template in selectedTemplates"
          :key="template.id"
          class="relative my-1 group">
          <span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
            {{ template.templateName }}

            <Icon
              icon="si:close-line"
              class="ml-1 cursor-pointer hover:text-white"
              @click.stop="deleteItem(template.templateName)" />
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
            v-for="template in filteredVendors"
            :key="template.id"
            class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600"
            @click="selectItem(template)">
            <span
              data-size="20"
              class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
              <span data-size="20">
                {{ template.templateName }}
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
