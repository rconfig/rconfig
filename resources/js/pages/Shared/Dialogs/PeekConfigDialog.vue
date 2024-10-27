<script setup lang="ts">
import Loading from '@/pages/Shared/Loading.vue';
import axios from 'axios';
import useClipboard from 'vue-clipboard3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, onUnmounted, onMounted, watch } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

const activeIcons = ref({});
const dialogStore = useDialogStore();
const fileLocation = ref('');
const hoverIcons = ref({});
const isLoading = ref(true);
const processedCode = ref('');
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'bash');
const { closeDialog, isDialogOpen } = dialogStore;
const { toClipboard } = useClipboard();
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

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
      selectedLanguage.value = localStorage.getItem('selectedPeekLanguage') || 'bash';
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

function copyPath(key, value) {
  toClipboard(value)
    .then(() => {
      activeIcons.value[key] = true;
      console.log('Copied:', JSON.stringify(value));
      setTimeout(() => {
        activeIcons.value[key] = false;
      }, 1500);
    })
    .catch(e => {
      console.error('Failed to copy:', e);
    });
}

// Add a watch for selectedLanguage
watch(selectedLanguage, (newValue, oldValue) => {
  console.log(`Language changed from ${oldValue} to ${newValue}`);
  localStorage.setItem('selectedPeekLanguage', newValue);
  showConfig();
});
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
                :icon="activeIcons['fileLocation'] ? 'material-symbols:check-circle-outline' : hoverIcons['fileLocation'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                :class="activeIcons['fileLocation'] ? 'text-green-500' : 'text-gray-500'"
                class="ml-2 cursor-pointer hover:text-gray-700"
                @mouseover="handleMouseOver('fileLocation')"
                @mouseleave="handleMouseLeave('fileLocation')"
                @click="copyPath('fileLocation', fileLocation)" />
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
