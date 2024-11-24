<script setup lang="ts">
import Loading from '@/pages/Shared/Loading.vue';
import axios from 'axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, onUnmounted, onMounted, watch } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
import { useClipboard } from '@vueuse/core';

const activeIcons = ref({});
const dialogStore = useDialogStore();
const fileLocation = ref('');
const hoverIcons = ref({});
const isLoading = ref(true);
const processedCode = ref('');
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'language-plaintext');
const { closeDialog, isDialogOpen } = dialogStore;
const { toastError } = useToaster();
const { text, copy, copied, isSupported } = useClipboard();

const props = defineProps({
  editId: Number
});

const languages = ['bash', 'c', 'cpp', 'css', 'html', 'java', 'javascript', 'language-plaintext', 'php', 'python', 'typescript'];

onMounted(() => {
  showConfig();
  window.addEventListener('keydown', handleKeyDown);
});

function showConfig() {
  processedCode.value = '';
  isLoading.value = true;
  axios
    .get('/api/configs/view-config/' + props.editId)
    .then(response => {
      // handle success
      processedCode.value = response.data.data.content;
      fileLocation.value = response.data.data.config_location;
      //   processedCode.value = processedCode.value.replace(/(\/\/.*)/g, '<span class="polcomment">$1</span>').replace(/(\#\[[^\]]+\])/g, '<span class="polmethod">$1</span>');
      selectedLanguage.value = localStorage.getItem('selectedPeekLanguage') || 'language-plaintext';
      isLoading.value = false;
    })
    .catch(error => {
      console.log(error);
      processedCode.value = 'Something went wrong - could not retrieve the template from the file system!';
      toastError('Error', 'Could not retrieve the template from the file system!');
      isLoading.value = false;
    });
}

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
  closeDialog('peek-config-dialog-' + props.editId);
}
</script>

<template>
  <Dialog :open="isDialogOpen('peek-config-dialog-' + editId)">
    <DialogTrigger as-child>
      <slot />
    </DialogTrigger>
    <DialogContent
      class="sm:max-w-[70dvw] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]"
      @escapeKeyDown="closeDialog('peek-config-dialog-' + editId)"
      @pointerDownOutside="closeDialog('peek-config-dialog-' + editId)"
      @closeClicked="closeDialog('peek-config-dialog-' + editId)">
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
        <div class="flex flex-col justify-between h-[100dvh]">
          <Loading v-if="isLoading" />
          <pre v-highlightjs><code :class="selectedLanguage">{{ processedCode }}
            </code></pre>
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
