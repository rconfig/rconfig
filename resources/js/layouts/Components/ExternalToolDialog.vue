<script setup>
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, inject } from 'vue';
import { useExternalLinksStore } from '@/stores/externalLinksStore';
const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
import { useToaster } from '@/composables/useToaster'; // Import the composable

const userid = inject('userid');
const isDialogOpen = ref(false);
const linkName = ref('');
const linkUrl = ref('');
const linkIcon = ref('');
const errors = ref([]);
const emit = defineEmits(['close']);
// Pinia store for managing external links
const externalLinksStore = useExternalLinksStore();

const openDialog = () => {
  isDialogOpen.value = true;
};

const closeDialog = () => {
  isDialogOpen.value = false;
  emit('close');
};

const submitLink = () => {
  axios
    .post('/api/users/add-external-link/' + userid, {
      name: linkName.value,
      url: linkUrl.value,
      icon: linkIcon.value
    })
    .then(response => {
      // Add the new link to the Pinia store
      console.log(response.data);
      externalLinksStore.setLinks(response.data);
      toastSuccess('External link added', 'The external link has been added successfully.');

      closeDialog();
    })
    .catch(error => {
      console.log(error);
      errors.value = error.response.data.errors;
      console.error('Error adding new link:', error);
      toastError('Error adding external link', 'There was an error adding the external link.');
    });
};
</script>

<template>
  <div>
    <Dialog :open="isDialogOpen">
      <DialogTrigger as-child>
        <Icon
          icon="bytesize:plus"
          @click="openDialog"
          class="ml-2 mr-1 cursor-pointer text-muted-foreground hover:text-white" />
      </DialogTrigger>
      <DialogContent
        class="sm:max-w-fit"
        @escapeKeyDown="closeDialog()"
        @pointerDownOutside="closeDialog()"
        @closeClicked="closeDialog()">
        <DialogTitle>Add New External Link</DialogTitle>
        <DialogDescription>Please fill in the details for the new external link you want to add.</DialogDescription>
        <div class="grid gap-2 py-4">
          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="linkName"
              class="text-right">
              Link Name
            </Label>
            <Input
              v-model="linkName"
              type="text"
              id="linkName"
              placeholder="Enter link name"
              required
              class="col-span-3" />
          </div>
          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="linkUrl"
              class="text-right">
              Link URL
            </Label>
            <Input
              v-model="linkUrl"
              type="text"
              id="linkUrl"
              placeholder="Enter link URL"
              required
              class="col-span-3" />
          </div>

          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="linkIcon"
              class="text-right">
              Link ICON
            </Label>
            <Input
              v-model="linkIcon"
              type="text"
              id="linkIcon"
              placeholder="Enter icon (e.g., mdi:tools)"
              required
              class="col-span-3" />
          </div>
          <span class="text-xs text-muted-foreground">
            Please select an icon from the
            <a
              class="text-blue-600"
              href="https://icon-sets.iconify.design/"
              target="_blank">
              iconify.design
            </a>
            icon sets. i.e. cib:cisco or simple-icons:junipernetworks
          </span>
        </div>

        <div class="flex flex-col w-full space-y-2">
          <span
            class="text-red-400"
            v-if="errors.name">
            {{ errors.name[0] }}
          </span>

          <span
            class="text-red-400"
            v-if="errors.url">
            {{ errors.url[0] }}
          </span>

          <span
            class="text-red-400"
            v-if="errors.icon">
            {{ errors.icon[0] }}
          </span>
        </div>

        <DialogFooter>
          <Button
            type="close"
            variant="outline"
            class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
            @click="closeDialog()"
            size="sm">
            Cancel
            <div class="pl-2 ml-auto">
              <kbd class="rc-kdb-class">ESC</kbd>
            </div>
          </Button>

          <Button
            type="submit"
            class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
            size="sm"
            @click="submitLink()"
            variant="primary">
            Add Link
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
  </div>
</template>
