<script setup>
import CategoryMultiSelect from '@/pages/Shared/FormFields/CategoryMultiSelect.vue';
import ConfirmCloseAlert from '@/pages/Shared/AlertDialog/ConfirmCloseAlert.vue';
import DeviceModelMultiSelect from '@/pages/Shared/FormFields/DeviceModelMultiSelect.vue';
import HelpPopover from '@/pages/Shared/Popover/HelpPopover.vue';
import TagMultiSelect from '@/pages/Shared/FormFields/TagMultiSelect.vue';
import TemplateMultiSelect from '@/pages/Shared/FormFields/TemplateMultiSelect.vue';
import VendorMultiSelect from '@/pages/Shared/FormFields/VendorMultiSelect.vue';
import Loading from '@/pages/Shared/Loading.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { InputPassword } from '@/components/ui/input-password';
import { ScrollArea } from '@/components/ui/scroll-area';
import { useAddEditDevices } from '@/pages/Inventory/Devices/useAddEditDevices';

const props = defineProps({
  editId: Number
});
const emit = defineEmits(['save', 'close']);

const { isLoading, model, saveDialog, errors, isDialogOpen, generatePrompts, showConfirmCloseAlert, showConfirmCloseDialog, cancelCloseDialog, confirmCloseDialog } = useAddEditDevices(props.editId, emit);
</script>

<template>
  <Dialog :open="isDialogOpen('DialogNewDevice')">
    <DialogTrigger as-child>
      <!-- <Button variant="outline">Edit Profile</Button> -->
    </DialogTrigger>
    <DialogContent
      @interactOutside="showConfirmCloseDialog()"
      @pointerDownOutside="showConfirmCloseDialog()"
      class="p-0 sm:max-w-7xl"
      @escapeKeyDown="showConfirmCloseDialog()"
      @closeClicked="showConfirmCloseDialog()">
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="text-sm text-rcgray-200">
          <div class="flex items-center">
            <DeviceIcon />
            <span class="ml-2">{{ editId > 0 ? 'Edit' : 'Add' }} Device {{ editId > 0 ? '(ID: ' + editId + ')' : '' }}</span>
            <span v-if="model.device_name">
              : &nbsp;
              <span class="text-muted-foreground">{{ model.device_name }}</span>
            </span>
          </div>
        </DialogTitle>
      </DialogHeader>

      <ScrollArea class="min-h-96 xl:h-auto">
        <Loading v-if="isLoading" />

        <transition name="fade">
          <div
            class="grid grid-cols-1 gap-6 p-4 lg:grid-cols-2"
            v-if="!isLoading">
            <!-- Column 1 -->
            <div class="flex flex-col col-span-1">
              <h3 class="mb-4 text-lg font-light">Device Information</h3>
              <Label
                for="device_name"
                class="mb-1 text-muted-foreground">
                Device Name
                <span class="text-red-400">*</span>
              </Label>
              <Input
                v-model="model.device_name"
                id="device_name"
                autocomplete="off"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_name">
                {{ errors.device_name[0] }}
              </span>

              <div class="flex flex-row space-x-4">
                <div class="flex flex-col w-1/2">
                  <Label
                    for="device_ip"
                    class="mt-4 mb-1 text-muted-foreground">
                    Device IP
                    <span class="text-red-400">*</span>
                  </Label>
                  <Input
                    v-model="model.device_ip"
                    id="device_ip"
                    autocomplete="off"
                    class="w-full" />
                  <span
                    class="text-red-400"
                    v-if="errors.device_ip">
                    {{ errors.device_ip[0] }}
                  </span>
                </div>

                <div class="flex flex-col w-1/2">
                  <Label
                    for="device_port_override"
                    class="mt-4 mb-1 text-muted-foreground">
                    Device Port
                    <HelpPopover
                      title="Device Port"
                      content="Set the connection port specific to this device. it overrides the value set in the connection template. Leave empty otherwise." />
                  </Label>
                  <Input
                    v-model="model.device_port_override"
                    id="device_port_override"
                    type="number"
                    autocomplete="off"
                    class="w-full" />
                </div>
              </div>

              <Label
                for="vector"
                class="mt-4 mb-1 text-muted-foreground">
                Vendor
                <span class="text-red-400">*</span>
              </Label>
              <VendorMultiSelect
                :singleSelect="true"
                v-model="model.device_vendor"
                id="device_vendor"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_vendor">
                {{ errors.device_vendor[0] }}
              </span>

              <Label
                for="device_category_id"
                class="mt-4 mb-1 text-muted-foreground">
                Category
                <span class="text-red-400">*</span>
              </Label>
              <CategoryMultiSelect
                v-model="model.selectedCategoryArray"
                id="selectedCategoryArray"
                :singleSelect="true"
                class="w-full" />

              <span
                class="text-red-400"
                v-if="errors.device_category_id">
                {{ errors.device_category_id[0] }}
              </span>

              <Label
                for="device_model"
                class="mt-4 mb-1 text-muted-foreground">
                Model
                <span class="text-red-400">*</span>
              </Label>

              <DeviceModelMultiSelect
                :singleSelect="true"
                v-model="model.selectedModelArray"
                id="selectedModelArray"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_model">
                {{ errors.device_model[0] }}
              </span>

              <Label
                for="device_tags"
                class="mt-4 mb-1 text-muted-foreground">
                Tags
                <span class="text-red-400">*</span>
              </Label>
              <TagMultiSelect
                v-model="model.device_tags"
                id="device_tags"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_tags">
                {{ errors.device_tags[0] }}
              </span>
            </div>

            <!-- Column 2 -->
            <div class="flex flex-col col-span-1">
              <h3 class="mb-4 text-lg font-light">Connection Information</h3>

              <Label
                for="device_username"
                class="mb-1 text-muted-foreground">
                Username
                <span class="text-red-400">*</span>
              </Label>
              <Input
                v-model="model.device_username"
                id="device_username"
                autocomplete="current-password"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_username">
                {{ errors.device_username[0] }}
              </span>

              <Label
                for="device_password"
                class="mt-4 mb-1 text-muted-foreground">
                Device Password
                <span class="text-red-400">*</span>
              </Label>
              <InputPassword
                v-model="model.device_password"
                id="device_password"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_password">
                {{ errors.device_password[0] }}
              </span>

              <Label
                for="device_enable_password"
                class="mt-4 mb-1 text-muted-foreground">
                Device Enable Password
              </Label>
              <InputPassword
                v-model="model.device_enable_password"
                id="device_enable_password"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_enable_password">
                {{ errors.device_enable_password[0] }}
              </span>

              <Label
                for="device_template"
                class="mt-4 mb-1 text-muted-foreground">
                Template
                <span class="text-red-400">*</span>
              </Label>
              <TemplateMultiSelect
                :singleSelect="true"
                v-model="model.device_template"
                id="device_template"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_template">
                {{ errors.device_template[0] }}
              </span>

              <Label
                for="main_prompt"
                class="mt-4 mb-1 text-muted-foreground">
                Main Prompt
                <HelpPopover
                  title="Device Main Prompt"
                  content="This is the 'Privileged EXEC' prompt. You will run show commands from this prompt and you can access configure mode. Usually 'router1#'" />
              </Label>
              <Input
                v-model="model.device_main_prompt"
                id="device_main_prompt"
                autocomplete="off"
                class="w-full" />
              <span
                class="text-red-400"
                v-if="errors.device_main_prompt">
                {{ errors.device_main_prompt[0] }}
              </span>
              <Label
                for="enable_prompt"
                class="mt-4 mb-1 text-muted-foreground">
                Enable Prompt
                <HelpPopover
                  title="Device Main Prompt"
                  content=" This is the 'User EXEC' prompt. The first level of access prompt. Usually 'router1>'" />
              </Label>
              <Input
                v-model="model.device_enable_prompt"
                id="device_enable_prompt"
                autocomplete="off"
                class="w-full" />

              <button
                class="flex items-center p-2 text-sm text-left rounded-lg pf-c-button pf-m-link pf-m-inline hover:bg-rcgray-800 max-w-fit text-muted-foreground hover:text-gray-200"
                type="button"
                @click="generatePrompts()">
                <Icon
                  icon="material-symbols:flash-auto"
                  class="w-4 h-4 mr-1" />
                Auto generate prompts from device name
              </button>

              <span
                class="text-red-400"
                v-if="errors.device_enable_prompt">
                {{ errors.device_enable_prompt[0] }}
              </span>
            </div>

            <!-- Column 3 -->
            <!-- <div class="flex flex-col col-span-1">
            Roles & Security
          </div> -->
          </div>
        </transition>
      </ScrollArea>

      <DialogFooter class="rc-dialog-footer bg-rcgray-800">
        <Button
          type="close"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="showConfirmCloseDialog()"
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
    @handleClose="cancelCloseDialog"
    @handleConfirm="confirmCloseDialog" />
</template>
