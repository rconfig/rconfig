<script setup>
import { ref, defineEmits, onMounted, onUnmounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDialogStore } from '@/stores/dialogActions';
import { Button } from '@/components/ui/button';

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(['save']);

defineProps({});

onMounted(() => {
  const handleKeyDown = event => {
    if (event.ctrlKey && event.key === 'Enter') {
      saveDialog();
    }
  };

  window.addEventListener('keydown', handleKeyDown);
});

const saveDialog = () => {
  console.log('Save dialog');
  emit('save');
  closeDialog('DialogNewTag');
};

// Cleanup event listener on unmount
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
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
      <div class="grid gap-4 py-4">
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="name"
            class="text-right">
            Tag Name
          </Label>
          <Input
            id="name"
            value="Pedro Duarte"
            class="col-span-3" />
        </div>
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="username"
            class="text-right">
            Description
          </Label>
          <Input
            id="username"
            value="@peduarte"
            class="col-span-3" />
        </div>
        <div class="grid items-center grid-cols-4 gap-4">
          <Label
            for="username"
            class="text-right">
            Roles
          </Label>
          <Input
            id="username"
            value="@peduarte"
            class="col-span-3" />
        </div>
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
