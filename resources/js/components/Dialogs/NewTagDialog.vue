<script setup>
import { ref, defineEmits, onMounted, onUnmounted } from 'vue';
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
  tagname: '',
  tagDescription: ''
});

defineProps({});

function handleKeyDown(event) {
  if (event.ctrlKey && event.key === 'Enter') {
    saveDialog();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function saveDialog() {
  axios
    .post('/api/tags', model.value)
    .then(response => {
      emit('save', response.data);
      toastSuccess('Tag created', 'The tag has been created successfully.');
      closeDialog('DialogNewTag');
    })
    .catch(error => {
      errors.value = error.response.data.errors;
    });
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewTag')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      class="sm:max-w-[425px]"
      @escapeKeyDown="closeDialog('DialogNewTag')"
      @pointerDownOutside="closeDialog('DialogNewTag')"
      @closeClicked="closeDialog('DialogNewTag')">
      <DialogHeader>
        <DialogTitle>Edit profile</DialogTitle>
        <DialogDescription>Make changes to your profile here. Click save when you're done.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-2 py-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="tagname"
            class="text-right">
            Tag Name
          </Label>
          <Input
            v-model="model.tagname"
            id="tagname"
            class="col-span-3" />
        </div>

        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="tagDescription"
            class="text-right">
            Description
          </Label>
          <Input
            v-model="model.tagDescription"
            id="tagDescription"
            class="col-span-3" />
        </div>
      </div>
      <div class="flex flex-col w-full space-y-2">
        <span
          class="text-red-400"
          v-if="errors.tagDescription">
          {{ errors.tagDescription[0] }}
        </span>

        <span
          class="text-red-400"
          v-if="errors.tagname">
          {{ errors.tagname[0] }}
        </span>
      </div>
      <DialogFooter>
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogNewTag')"
          size="sm">
          Cancel
          <div class="pl-2 ml-auto">
            <kbd class="bxnAJf">ESC</kbd>
          </div>
        </Button>
        <Button
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
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
