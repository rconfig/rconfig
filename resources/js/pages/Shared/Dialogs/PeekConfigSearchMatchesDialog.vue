<script setup>
import Loading from '@/pages/Shared/Loading.vue';
import axios from 'axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, onUnmounted, onMounted, computed } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { useClipboard } from '@vueuse/core';

const activeIcons = ref({});
const dialogStore = useDialogStore();
const fileLocation = ref('');
const hoverIcons = ref({});
const isLoading = ref(true);
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'language-plaintext');
const { closeDialog, isDialogOpen } = dialogStore;
const { toastError } = useToaster();
const { text, copy, copied, isSupported } = useClipboard();

const props = defineProps({
  editId: {
    type: Number,
    required: true
  },
  record: {
    type: Object,
    required: true
  },
  searchString: {
    type: String,
    required: true
  }
});
// Destructuring props
const { matches } = props.record;
const languages = ['bash', 'c', 'cpp', 'css', 'html', 'java', 'javascript', 'language-plaintext', 'php', 'python', 'typescript'];

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
  isLoading.value = false;
});

// Methods
const highlightMatch = context => {
  if (!props.searchString) return context;

  // Create a regular expression to highlight all occurrences of searchString
  const regex = new RegExp(props.searchString, 'gi');
  const highlightedContext = context.replace(regex, matched => `<span class="highlightMatch">${matched}</span>`);

  // Replace line breaks with <br> to ensure line breaks are preserved in HTML
  return highlightedContext.replace(/\n/g, '<br>');
};

function handleKeyDown(event) {
  if (event.key === 'Escape') {
    handleClose();
  }
}

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

const handleMouseOver = key => {
  hoverIcons.value[key] = true;
};

const handleMouseLeave = key => {
  hoverIcons.value[key] = false;
};

function handleClose() {
  closeDialog('peek-config-search-matches-dialog-' + props.editId);
}
</script>

<template>
  <Dialog :open="isDialogOpen('peek-config-search-matches-dialog-' + editId)">
    <DialogTrigger as-child>
      <slot />
    </DialogTrigger>
    <DialogContent
      class="sm:max-w-[70dvw] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]"
      @escapeKeyDown="closeDialog('peek-config-search-matches-dialog-' + editId)"
      @pointerDownOutside="closeDialog('peek-config-search-matches-dialog-' + editId)"
      @closeClicked="closeDialog('peek-config-search-matches-dialog-' + editId)">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>Configuration Quick Peek (Config ID: {{ editId }})</DialogTitle>
        <DialogDescription>
          <div class="flex justify-between">
            <div class="flex items-center">
              <div class="mr-2">Language Options:</div>
              <Select v-model="selectedLanguage">
                <SelectTrigger class="w-[180px]">
                  <SelectValue placeholder="Select a language" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem
                      :value="language"
                      v-for="language in languages"
                      :key="language">
                      {{ language }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>
            <div class="flex items-center">
              Path: {{ fileLocation }}
              <Icon
                :icon="copied ? 'material-symbols:check-circle-outline' : !copied ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                :class="copied ? 'text-green-500' : 'text-gray-500'"
                class="ml-2 cursor-pointer hover:text-gray-700"
                @mouseover="handleMouseOver('fileLocation')"
                @mouseleave="handleMouseLeave('fileLocation')"
                @click="copy(fileLocation)" />
            </div>
          </div>
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-4 px-6 py-4 overflow-y-auto">
        <div class="flex flex-col justify-between">
          <Loading v-if="isLoading" />
          <pre v-highlightjs>
        <code :class="selectedLanguage">
           <div v-for="(match, index) in matches" :key="index" class="context-block">
<span class="underline">Matched Line: {{ match.line_number }}</span>
      <pre v-html="highlightMatch(match.context)"></pre>
    </div>
        </code>
      </pre>
        </div>
      </div>
      <DialogFooter class="p-6 pt-0">
        <Button
          @click="handleClose()"
          variant="outline">
          Close
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style>
.highlightMatch {
  background-color: #df8e1d !important;
  font-weight: bold !important;
  color: #000 !important;
}
</style>
