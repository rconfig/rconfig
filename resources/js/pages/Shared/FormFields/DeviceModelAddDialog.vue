<script setup>
import axios from "axios";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Plus } from "lucide-vue-next";
import { ref, onMounted, onUnmounted } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess } = useToaster();

const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;

const emit = defineEmits(["save"]);
const errors = ref([]);
const model = ref({
  	name: "",
});

const props = defineProps({
	editId: {
		type: Number,
		default: 0,
	},
});

function handleKeyDown(event) {
	if (event.ctrlKey && event.key === "Enter") {
		saveDialog();
	}
}

onMounted(() => {
  	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
  	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	axios
		.post("/api/add-device-model", { name: model.value.name })
		.then(() => {
		emit("save");
		toastSuccess("Device model created", "The device model has been created successfully.");
		closeDialog("DialogDeviceModelAdd");
		model.value = { name: "" };
		errors.value = [];
		})
		.catch((error) => {
		errors.value = error.response?.data?.errors || {};
		});
}
</script>

<template>
  <Dialog :open="isDialogOpen('DialogDeviceModelAdd')">
    <DialogTrigger as-child>
      <Button variant="ghost" @click="openDialog('DialogDeviceModelAdd')" class="justify-start w-full p-1">
        <Plus size="16" class="w-3 h-3 mt-1 mr-2 text-muted-foreground" />
        <span>Add new device model</span>
      </Button>
    </DialogTrigger>

    <DialogContent
      class="p-0 sm:max-w-1/3"
      @escapeKeyDown="closeDialog('DialogDeviceModelAdd')"
      @pointerDownOutside="closeDialog('DialogDeviceModelAdd')"
      @closeClicked="closeDialog('DialogDeviceModelAdd')"
    >
      <DialogHeader class="rc-dialog-header">
        <DialogTitle class="text-sm text-rcgray-200">
          <div class="flex items-center">
            <RcIcon name="user" class="w-5 h-5" />
            <span class="ml-2">
              {{ editId > 0 ? 'Edit' : 'Add' }} Device Model
              {{ editId > 0 ? "(ID: " + editId + ")" : "" }}
            </span>
          </div>
        </DialogTitle>
      </DialogHeader>

      <div class="grid gap-2 p-4">
        <div class="grid items-center grid-cols-1 gap-4">
          <Label for="name" class="text-left">
            Device model name <span class="text-red-400">*</span>
          </Label>
          <Input v-model="model.name" id="name" class="col-span-3" autocomplete="off" />
          <span class="col-start-2 -mt-4 text-sm text-red-400" v-if="errors.name">
            {{ errors.name[0] }}
          </span>
        </div>
      </div>

      <DialogFooter class="rc-dialog-footer bg-rcgray-800">
        <Button
          type="button"
          variant="outline"
          class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
          @click="closeDialog('DialogDeviceModelAdd')"
          size="sm"
        >
          Cancel
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class">ESC</kbd>
          </div>
        </Button>

        <Button
          type="submit"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
          size="sm"
          @click="saveDialog()"
          variant="primary"
        >
          {{ props.editId === 0 ? 'Save' : 'Update' }}
          <div class="pl-2 ml-auto">
            <kbd class="rc-kdb-class2">
              Ctrl&nbsp;
              <RcIcon name="enter" class="ml-1" />
            </kbd>
          </div>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
