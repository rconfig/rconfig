<script setup>
import ConfirmCloseAlert from '@/pages/Shared/AlertDialog/ConfirmCloseAlert.vue';
import NavPanel from '@/pages/Tasks/WizardPanels/NavPanel.vue';
import Step1 from '@/pages/Tasks/WizardPanels/Step1.vue';
import Step2 from '@/pages/Tasks/WizardPanels/Step2.vue';
import Step3 from '@/pages/Tasks/WizardPanels/Step3.vue';
import Step4 from '@/pages/Tasks/WizardPanels/Step4.vue';
import Step5 from '@/pages/Tasks/WizardPanels/Step5.vue';
import axios from 'axios';
import { Dialog, DialogContent, DialogScrollContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);
const showConfirmCloseAlert = ref(false);
const errors = ref([]);
const model = ref({
  task_name: '',
  task_desc: '',
  task_command: '',
  task_categories: 0,
  task_devices: 0,
  task_tags: 0,
  is_system: 0,
  download_report_notify: true,
  verbose_download_report_notify: false,
  task_cron: ['*', '*', '*', '*', '*'],
  device: [],
  category: [],
  tag: []
});

const activeStep = ref(1);

const props = defineProps({
  editId: Number
});

function handleKeyDown(event) {
  if (event.ctrlKey && event.key === 'Enter') {
    saveDialog();
  }
}

onMounted(() => {
  if (props.editId > 0) {
    axios.get(`/api/tasks/${props.editId}`).then(response => {
      model.value = response.data;
    });
  }

  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  let id = props.editId > 0 ? `/${props.editId}` : ''; // determine if we are creating or updating
  let method = props.editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating
  axios[method]('/api/tasks' + id, model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('Task created', 'The tag has been created successfully.');
      closeDialog('DialogNewTask');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}

function prevPage() {
  activeStep.value--;
}

function nextPage() {
  errors.value = '';
  if (activeStep.value === 1 && (!model.value.task_command || model.value.task_command === '')) {
    errors.value = 'Please select a task type';
    return;
  }

  if (activeStep.value === 2 && model.value.task_name === '') {
    errors.value = 'Please enter a task name';
    return;
  }

  if (activeStep.value === 3 && model.value.task_command === 'rconfig:download-device' && (!model.value.device || model.value.device.length === 0)) {
    errors.value = 'Please choose one or more devices';
    return;
  }

  if (activeStep.value === 3 && model.value.task_command === 'rconfig:download-category' && (!model.value.category || model.value.category.length === 0)) {
    errors.value = 'Please choose one or more categories';
    return;
  }

  if (activeStep.value === 3 && model.value.task_command === 'rconfig:download-tag' && (!model.value.tag || model.value.tag.length === 0)) {
    errors.value = 'Please choose one or more tags';
    return;
  }

  if (activeStep.value === 4 && model.value.task_cron === '') {
    errors.value = 'Please enter schedule values';
    return;
  }

  activeStep.value++;

  // if (wizard.currentPage === 5) {
  //   // saveModels();
  // } else {
  //   wizard.currentPage++;
  // }
}
function confirmCloseWizardOpen() {
  showConfirmCloseAlert.value = true;
}
function cancelCloseWizard() {
  showConfirmCloseAlert.value = false;
}
function confirmCloseWizard() {
  showConfirmCloseAlert.value = false;
  closeDialog('DialogNewTask');
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewTask')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      @interactOutside="confirmCloseWizardOpen()"
      @pointerDownOutside="confirmCloseWizardOpen()"
      class="max-w-4xl gap-0 p-0 m-4 bg-rcgray-900"
      @escapeKeyDown="closeDialog('DialogNewTask')"
      @closeClicked="closeDialog('DialogNewTask')">
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="text-sm text-rcgray-200">
          <div class="flex items-center">
            <Icon icon="catppuccin:esbuild" />
            <span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} Task {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}</span>
          </div>
        </DialogTitle>
        <!-- <DialogDescription>Make changes to your tag here. Click {{ editId > 0 ? 'update' : 'save' }} when you're done.</DialogDescription> -->
      </DialogHeader>
      <ResizablePanelGroup
        direction="horizontal"
        class="p-4">
        <ResizablePanel
          :default-size="25"
          :max-size="25"
          :min-size="25"
          collapsible
          :collapsed-size="25"
          ref="panelElement"
          class="">
          <NavPanel :currentStep="activeStep" />
        </ResizablePanel>
        <ResizableHandle />
        <ResizablePanel class="max-h-[80vh]">
          <ScrollArea class="h-full px-4 border border-none rounded-md">
            <Step1
              v-if="activeStep === 1"
              :model="model" />
            <Step2
              v-if="activeStep === 2"
              :model="model" />
            <Step3
              :model="model"
              v-if="activeStep === 3" />
            <Step4
              :model="model"
              v-if="activeStep === 4" />
            <Step5
              :model="model"
              v-if="activeStep === 5" />
          </ScrollArea>
        </ResizablePanel>
      </ResizablePanelGroup>
      <div class="w-full py-2 border-t border-b">
        <div
          class="flex items-center px-2"
          :class="errors.length > 0 ? 'justify-between' : 'justify-end'">
          <div
            v-if="errors.length"
            class="text-sm text-red-500">
            {{ errors }}
          </div>
          <div class="flex">
            <Button
              :disabled="activeStep === 1"
              @click.prevent="prevPage()"
              variant="outline"
              class="px-2 py-1 text-sm hover:animate-pulse"
              size="sm">
              Previous
            </Button>
            <Button
              :disabled="activeStep === 5"
              @click.prevent="nextPage()"
              variant="outline"
              class="px-2 py-1 ml-2 text-sm hover:animate-pulse"
              size="sm">
              Next
            </Button>
          </div>
        </div>
      </div>

      <DialogFooter class="rc-dialog-footer bg-rcgray-800">
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="confirmCloseWizardOpen()"
          size="sm">
          Cancel
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class">ESC</kbd>
          </div>
        </Button>

        <Button
          v-if="props.editId === 0"
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click="saveDialog()"
          variant="primary">
          Save
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class2">
              Ctrl&nbsp;
              <Icon
                icon="uil:enter"
                class="" />
            </kbd>
          </div>
        </Button>

        <Button
          v-if="props.editId > 0"
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click="saveDialog()"
          variant="primary">
          Update
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class2">
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
  <ConfirmCloseAlert
    :showConfirmCloseAlert="showConfirmCloseAlert"
    @handleClose="cancelCloseWizard"
    @handleConfirm="confirmCloseWizard" />
</template>
