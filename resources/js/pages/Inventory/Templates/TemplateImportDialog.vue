<script setup>
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import axios from 'axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useTemplatesGithub } from '@/pages/Inventory/Templates/useTemplatesGithub';
import { useToaster } from '@/composables/useToaster'; // Import the composable

const { importingTemplates, getTemplateRepoFolders, vendorOptionSelected, showFileOptions, listedFiles, hasReadmeFile, fileOptionSelected, selectedTemplateCode } = useTemplatesGithub();

const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['setTemplateCode']);
const errors = ref([]);
const model = ref({});

const props = defineProps({
  editId: Number
});

function handleKeyDown(event) {
  if (event.ctrlKey && event.key === 'Enter') {
    saveDialog();
  }
}

onMounted(() => {
  getTemplateRepoFolders();
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  emit('setTemplateCode', selectedTemplateCode);
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogTemplateImport')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="w-full"
      @escapeKeyDown="closeDialog('DialogTemplateImport')"
      @pointerDownOutside="closeDialog('DialogTemplateImport')"
      @closeClicked="closeDialog('DialogTemplateImport')">
      <DialogHeader>
        <DialogTitle>Select from imported templates</DialogTitle>
        <DialogDescription>Choose a vendor and select a template to import. You may edit the template after importing.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-2 py-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Select v-model="vendorOptionSelected">
            <SelectTrigger className="w-[280px] flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start w-[180px]">
              <SelectValue placeholder="Select a template vendor" />
            </SelectTrigger>
            <SelectContent position="popper">
              <SelectGroup>
                <Spinner
                  :state="importingTemplates"
                  class="items-center" />
                <SelectItem
                  v-for="option in vendorTemplateOptions.data"
                  :key="option.name"
                  :value="option.path.toString()">
                  {{ option.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
        <div class="grid items-center grid-cols-4 gap-4">
          <Select
            v-if="showFileOptions"
            v-model="fileOptionSelected">
            <SelectTrigger className="w-[280px] flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start w-[180px]">
              <SelectValue placeholder="Select a template file" />
            </SelectTrigger>
            <SelectContent position="popper">
              <SelectGroup>
                <SelectItem
                  v-for="vendor in listedFiles.data"
                  :key="vendor.name"
                  :value="vendor.path.toString()">
                  {{ vendor.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
      </div>
      <div
        class="flex flex-col w-full space-y-2 text-sm text-muted-foreground"
        v-if="fileOptionSelected">
        CLick apply to load this template into the editor.
      </div>
      <DialogFooter>
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogTemplateImport')"
          size="sm">
          Cancel
          <div class="pl-2 ml-auto">
            <kbd class="bxnAJf">ESC</kbd>
          </div>
        </Button>

        <Button
          v-if="selectedTemplateCode"
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click="saveDialog()"
          variant="primary">
          Apply
          <div class="pl-2 ml-auto">
            <kbd class="bxnAJf2">
              Ctrl&nbsp;
              <Icon
                icon="uil:enter"
                class="" />
            </kbd>
          </div>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
