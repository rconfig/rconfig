<script setup>
import axios from 'axios';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { InputPassword } from '@/components/ui/input-password';
import { ref, onMounted, onUnmounted } from 'vue';
import { useDialogStore } from '@/stores/dialogActions';
import { useToaster } from '@/composables/useToaster'; // Import the composable

const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);
const errors = ref([]);
const model = ref({
  cred_name: '',
  cred_description: ''
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
    axios.get(`/api/device-credentials/${props.editId}`).then(response => {
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
  axios[method]('/api/device-credentials' + id, model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('Credential created', 'The credential has been created successfully.');
      closeDialog('DialogNewCred');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewCred')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="w-full"
      @escapeKeyDown="closeDialog('DialogNewCred')"
      @pointerDownOutside="closeDialog('DialogNewCred')"
      @closeClicked="closeDialog('DialogNewCred')">
      <DialogHeader>
        <DialogTitle>
          <h3 class="flex items-center mb-2 text-xl font-semibold leading-7 tracking-tight font-inter">
            <CredentialsIcon class="mr-2" />
            {{ editId > 0 ? 'Edit' : 'Add' }} Credential {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}
          </h3>
        </DialogTitle>
        <DialogDescription>Make changes to your credential here. Click {{ editId > 0 ? 'update' : 'save' }} when you're done.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-2 py-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="cred_name"
            class="text-right">
            Name
            <span class="text-red-400">*</span>
          </Label>
          <Input
            v-model="model.cred_name"
            id="cred_name"
            class="col-span-3"
            autocomplete="off" />
          <span
            class="col-span-2 col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.cred_name">
            {{ errors.cred_name[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="cred_description"
            class="text-right">
            Description
          </Label>
          <Input
            v-model="model.cred_description"
            id="cred_description"
            class="col-span-3" />
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="cred_username"
            class="text-right">
            Username
            <span class="text-red-400">*</span>
          </Label>
          <Input
            v-model="model.cred_username"
            id="cred_username"
            class="col-span-3"
            autocomplete="off" />
          <span
            class="col-span-2 col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.cred_username">
            {{ errors.cred_username[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="cred_enable_password"
            class="text-right">
            Password
            <span class="text-red-400">*</span>
          </Label>
          <InputPassword
            v-model="model.cred_password"
            id="cred_password"
            mainDivClass="col-span-3" />
          <span
            class="col-span-2 col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.cred_password">
            {{ errors.cred_password[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="cred_enable_password"
            class="text-right">
            Enable Password
          </Label>
          <InputPassword
            v-model="model.cred_enable_password"
            id="cred_enable_password"
            mainDivClass="col-span-3" />
        </div>
      </div>

      <DialogFooter>
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogNewCred')"
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
