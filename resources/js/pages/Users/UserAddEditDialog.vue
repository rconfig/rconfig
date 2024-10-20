<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

import { useDialogStore } from '@/stores/dialogActions';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { useToaster } from '@/composables/useToaster'; // Import the composable
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);
const roles = ref([]);
const errors = ref([]);
const model = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  repeat_password: '',
  role: ''
});
const successMessage = ref('');

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
    axios.get(`/api/users/${props.editId}`).then(response => {
      model.value = response.data;
    });
  }

  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

watch(
  () => [model.value.password, model.value.repeat_password],
  () => {
    if (model.value.password !== model.value.repeat_password) {
      successMessage.value = '';
      errors.value.repeat_password = ['Passwords do not match'];
    } else {
      delete errors.value.repeat_password;
      successMessage.value = 'Passwords match';
    }
  }
);

function saveDialog() {
  let id = props.editId > 0 ? `/${props.editId}` : ''; // determine if we are creating or updating
  let method = props.editId > 0 ? 'patch' : 'post'; // determine if we are creating or updating
  axios[method]('/api/users' + id, model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('User created', 'The tag has been created successfully.');
      closeDialog('DialogNewUser');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewUser')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="p-0 sm:max-w-fit"
      @escapeKeyDown="closeDialog('DialogNewUser')"
      @pointerDownOutside="closeDialog('DialogNewUser')"
      @closeClicked="closeDialog('DialogNewUser')">
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="text-sm text-rcgray-200">
          <div class="flex items-center">
            <UserIcon />
            <span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} User {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}</span>
          </div>
        </DialogTitle>
        <!-- <DialogDescription>Make changes to your tag here. Click {{ editId > 0 ? 'update' : 'save' }} when you're done.</DialogDescription> -->
      </DialogHeader>
      <div class="grid gap-2 p-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="name"
            class="text-right">
            Name
            <span class="text-red-400">*</span>
          </Label>
          <Input
            v-model="model.name"
            id="name"
            class="col-span-3"
            autocomplete="off" />
          <span
            class="col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.name">
            {{ errors.name[0] }}
          </span>
        </div>
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="username"
            class="text-right">
            User Name
          </Label>
          <Input
            v-model="model.username"
            id="username"
            class="col-span-3" />
          <span
            class="col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.username">
            {{ errors.username[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="email"
            class="text-right">
            Email
            <span class="text-red-400">*</span>
          </Label>
          <Input
            v-model="model.email"
            id="email"
            type="email"
            class="col-span-3" />
          <span
            class="col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.email">
            {{ errors.email[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="password"
            class="text-right">
            Password
            <span class="text-red-400">*</span>
          </Label>
          <Input
            type="password"
            v-model="model.password"
            id="password"
            class="col-span-3" />
          <span
            class="col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.password && editId === 0">
            {{ errors.password[0] }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="repeat_password"
            class="text-right">
            Repeat Password
            <span class="text-red-400">*</span>
          </Label>
          <Input
            type="password"
            v-model="model.repeat_password"
            id="repeat_password"
            class="col-span-3" />
          <span
            class="col-start-2 -mt-4 text-sm text-red-400"
            v-if="errors.repeat_password && editId === 0">
            {{ errors.repeat_password[0] }}
          </span>
          <span
            class="col-start-2 -mt-4 text-sm text-green-400"
            v-if="successMessage && editId === 0">
            {{ successMessage }}
          </span>
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="repeat_password"
            class="text-right">
            Role
            <span class="text-red-400">*</span>
          </Label>
          <Select v-model="model.role">
            <SelectTrigger class="col-span-3">
              <SelectValue placeholder="Select a role" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem value="admin">Admin</SelectItem>
                <SelectItem value="user">User</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
      </div>

      <DialogFooter class="rc-dialog-footer bg-rcgray-800">
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogNewUser')"
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
