<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDialogStore } from '@/stores/dialogActions';
import { Button } from '@/components/ui/button';
import axios from 'axios';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);
const roles = ref([]);
const errors = ref([]);
const model = ref({
  vendorName: ''
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
    axios.get(`/api/vendors/${props.editId}`).then(response => {
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
  axios[method]('/api/vendors' + id, model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('Vendor created', 'The vendor has been created successfully.');
      closeDialog('DialogNewVendor');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewVendor')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="w-full"
      @escapeKeyDown="closeDialog('DialogNewVendor')"
      @pointerDownOutside="closeDialog('DialogNewVendor')"
      @closeClicked="closeDialog('DialogNewVendor')">
      <DialogHeader>
        <DialogTitle>{{ editId > 0 ? 'Edit' : 'Add' }} Vendor {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}</DialogTitle>
        <DialogDescription>Make changes to your vendor here. Click {{ editId > 0 ? 'update' : 'save' }} when you're done.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-2 py-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="vendorName"
            class="text-right">
            Vendor Name
          </Label>
          <Input
            v-model="model.vendorName"
            id="vendorName"
            class="col-span-3" />
        </div>
      </div>
      <div class="flex flex-col w-full space-y-2">
        <span
          class="text-red-400"
          v-if="errors.vendorName">
          {{ errors.vendorName[0] }}
        </span>
      </div>
      <DialogFooter>
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogNewVendor')"
          size="sm">
          Cancel
          <div class="pl-2 ml-auto">
            <kbd class="bxnAJf">ESC</kbd>
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
            <kbd class="bxnAJf2">
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
