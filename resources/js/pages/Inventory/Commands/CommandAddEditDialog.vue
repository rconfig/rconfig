<script setup>
import axios from 'axios';
import CategoryMultiSelect from '@/pages/Shared/FormFields/CategoryMultiSelect.vue';
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);
const errors = ref([]);
const isLoading = ref(false);
const model = ref({
  command: '',
  description: '',
  category: []
});

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
    isLoading.value = true;
    axios.get(`/api/commands/${props.editId}`).then(response => {
      model.value = response.data;
      model.value.categoryArray = response.data.category.map(cat => cat.id);
    });
    isLoading.value = false;
  }

  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  let id = props.editId > 0 ? `/${props.editId}` : ''; // determine if we are creating or updating
  let method = props.editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating
  axios[method]('/api/commands' + id, model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('Command created', 'The command has been created successfully.');
      closeDialog('DialogNewCommand');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewCommand')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="p-0 sm:max-w-fit"
      @escapeKeyDown="closeDialog('DialogNewCommand')"
      @pointerDownOutside="closeDialog('DialogNewCommand')"
      @closeClicked="closeDialog('DialogNewCommand')">
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="text-sm text-rcgray-200">
          <div class="flex items-center">
            <CommandsIcon />
            <span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} Command {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}</span>
          </div>
        </DialogTitle>
        <!-- <DialogDescription>Make changes to your tag here. Click {{ editId > 0 ? 'update' : 'save' }} when you're done.</DialogDescription> -->
      </DialogHeader>

      <Spinner :state="isLoading" />
      <div
        class="grid gap-2 p-4"
        v-if="!isLoading">
        <div class="grid items-center grid-cols-4 gap-2">
          <Label
            for="command"
            class="text-right">
            Command Name
            <span class="text-red-400">*</span>
          </Label>
          <Input
            v-model="model.command"
            id="command"
            class="col-span-3" />
          <span
            class="col-span-3 col-start-2 text-sm text-red-400"
            v-if="errors.command">
            {{ errors.command[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-2">
          <Label
            for="description"
            class="text-right">
            Description
          </Label>
          <Input
            v-model="model.description"
            id="description"
            class="col-span-3" />
        </div>

        <div class="grid items-center grid-cols-4 gap-2">
          <Label
            for="description"
            class="text-right">
            Command Groups
            <span class="text-red-400">*</span>
          </Label>
          <CategoryMultiSelect v-model="model.category" />
          <span
            class="col-span-3 col-start-2 text-sm text-red-400"
            v-if="errors.category">
            {{ errors.category[0] }}
          </span>
        </div>
      </div>

      <DialogFooter class="rc-dialog-footer bg-rcgray-800">
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogNewCommand')"
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
</template>
