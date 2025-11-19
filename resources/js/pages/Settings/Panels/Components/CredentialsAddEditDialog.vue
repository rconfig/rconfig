<script setup>
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { InputPassword } from "@/components/ui/input-password";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Switch } from "@/components/ui/switch";
import { Textarea } from "@/components/ui/textarea";
import { ref, onMounted, onUnmounted, watchEffect } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

const { toastSuccess, toastError, toastInfo, toastWarning, toastDefault } = useToaster();
const dialogStore = useDialogStore();
const { openDialog, closeDialog, isDialogOpen } = dialogStore;
const emit = defineEmits(["save", "showConfirmModal"]);
const errors = ref([]);
const selectedMethod = ref("usernamePassword");
const isLoading = ref(false); // Start with false for new credentials
const model = ref({
	cred_name: "",
	cred_description: "",
	cred_username: "",
	cred_password: "",
	cred_enable_password: "",
	ssh_key: "",
	ssh_key_passphrase: "",
	rotate_creds: false,
	vault_enabled: 0,
	cred_is_default: 0,
});

const props = defineProps({
	editId: {
		type: Number,
		default: 0,
	},
	confirmRotation: {
		type: Boolean,
		default: false,
	},
});

function handleKeyDown(event) {
	if (event.ctrlKey && event.key === "Enter") {
		saveDialog();
	}
}

onMounted(async () => {
	// Only set loading to true if we're editing an existing credential
	if (props.editId > 0) {
		isLoading.value = true;
		try {
			const response = await axios.get(`/api/settings/credentials/${props.editId}`);
			model.value = response.data;
			if (response.data.ssh_key) {
				selectedMethod.value = "privateKey";
			} else {
				selectedMethod.value = "usernamePassword";
			}
		} catch (error) {
			console.error("Error loading credential:", error);
			// Handle error appropriately
		} finally {
			isLoading.value = false;
		}
	}
	// For new credentials, isLoading stays false since no data needs to be fetched

	window.addEventListener("keydown", handleKeyDown);
});

watchEffect(() => {
	model.value.rotate_creds = props.confirmRotation;
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

function saveDialog() {
	let id = props.editId > 0 ? `/${props.editId}` : "";
	let method = props.editId > 0 ? "patch" : "post";
	const payload = {
		...model.value,
		selectedMethod: selectedMethod.value,
	};
	axios[method]("/api/settings/credentials" + id, payload)
		.then((response) => {
			emit("save", response.data);
			toastSuccess("Credential Created", "Credential has been created successfully");
			closeDialog("DialogNewCred");
		})
		.catch((error) => {
			errors.value = error.response.data.errors;
		});
}

function validateRotation(value) {
	if (value === true) {
		emit("showConfirmModal", value);
	}
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewCred')">
		<DialogTrigger as-child>
			<!-- <Button variant="outline">Edit Profile</Button> -->
		</DialogTrigger>
		<DialogContent class="max-w-4xl" @escapeKeyDown="closeDialog('DialogNewCred')" @pointerDownOutside="closeDialog('DialogNewCred')" @closeClicked="closeDialog('DialogNewCred')">
			<DialogHeader>
				<DialogTitle>
					<h3 class="flex items-center rc-panel-heading">
						<RcIcon name="credentials" class="mr-2" />
						{{ editId > 0 ? "Edit Credential" : "Add Credential" }} {{ editId > 0 ? "(ID: " + editId + ")" : "" }}
					</h3>
				</DialogTitle>
				<DialogDescription>{{ editId > 0 ? "Edit the credential details below" : "Add a new credential by filling in the details below" }}</DialogDescription>
			</DialogHeader>
			<div class="grid gap-4 py-4">
				<div class="grid items-center grid-cols-4 gap-4">
					<Label for="cred_name" class="text-right">
						<span class="inline-flex items-center gap-1">
							Name
							<span class="text-red-400">*</span>
						</span>
					</Label>
					<Skeleton class="h-8 col-span-3" v-if="isLoading" />
					<Input v-model="model.cred_name" v-else id="cred_name" class="col-span-3" autocomplete="off" :disabled="editId > 0" />
					<span class="col-span-2 col-start-2 -mt-4 text-sm text-red-400" v-if="errors.cred_name">
						{{ errors.cred_name[0] }}
					</span>
				</div>

				<div class="grid items-center grid-cols-4 gap-4">
					<Label for="cred_description" class="text-right">
						<span class="inline-flex items-center gap-1">
							Description
						</span>
					</Label>
					<Skeleton class="h-8 col-span-3" v-if="isLoading" />
					<Input v-model="model.cred_description" v-else id="cred_description" class="col-span-3" :disabled="editId > 0" />
				</div>

				<div class="grid items-center grid-cols-4 gap-4">
					<Label class="text-right">Authentication Method</Label>
					<div class="col-span-3">
						<template v-if="isLoading">
							<Skeleton class="h-6 w-64" />
						</template>
						<RadioGroup v-else v-model="selectedMethod" class="flex space-x-6">
							<label class="flex items-center space-x-2 cursor-pointer">
								<RadioGroupItem value="usernamePassword" id="usernamePassword" class="h-5 w-5 rounded-full focus:ring-2 focus:ring-blue-400 focus:outline-none data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-600" />
								<span class="text-sm font-medium rc-text-sm-muted">Username/Password</span>
							</label>
						</RadioGroup>
					</div>
				</div>

				<div class="grid items-center grid-cols-4 gap-4">
					<Label for="cred_username" class="text-right">
						Username
						<span class="text-red-400">*</span>
					</Label>
					<Skeleton class="h-8 col-span-3" v-if="isLoading" />
					<Input v-model="model.cred_username" v-else id="cred_username" class="col-span-3" autocomplete="off" />
					<span class="col-span-2 col-start-2 -mt-4 text-sm text-red-400" v-if="errors.cred_username">
						{{ errors.cred_username[0] }}
					</span>
				</div>

				<template v-if="!isLoading && selectedMethod === 'usernamePassword'">
					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="cred_password" class="text-right">
							Password
							<span class="text-red-400">*</span>
						</Label>
						<InputPassword v-model="model.cred_password" id="cred_password" mainDivClass="col-span-3" />
						<span class="col-span-2 col-start-2 -mt-4 text-sm text-red-400" v-if="errors.cred_password">
							{{ errors.cred_password[0] }}
						</span>
					</div>

					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="cred_enable_password" class="text-right">
							Enable Password
						</Label>
						<InputPassword v-model="model.cred_enable_password" id="cred_enable_password" mainDivClass="col-span-3" />
						<span class="col-span-2 col-start-2 -mt-4 text-sm text-red-400" v-if="errors.cred_enable_password">
							{{ errors.cred_enable_password[0] }}
						</span>
					</div>
				</template>

				<template v-if="isLoading && selectedMethod === 'usernamePassword'">
					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="cred_password" class="text-right">
							Password
							<span class="text-red-400">*</span>
						</Label>
						<Skeleton class="h-8 col-span-3" />
					</div>

					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="cred_enable_password" class="text-right">
							Enable Password
						</Label>
						<Skeleton class="h-8 col-span-3" />
					</div>
				</template>
				<div v-if="!isLoading && editId > 0" class="grid items-center grid-cols-4 gap-4">
					<Label for="rotate_creds" class="text-right">
						Rotate Device Credentials
					</Label>
					<div class="col-span-3">
						<Switch v-model:checked="model.rotate_creds" id="rotate_creds" @update:checked="validateRotation" />
						<span class="ml-2 text-sm text-gray-600" v-if="model.rotate_creds">Device credentials will be rotated</span>
					</div>
				</div>

				<div v-if="isLoading && editId > 0" class="grid items-center grid-cols-4 gap-4">
					<Label for="rotate_creds" class="text-right">
						Rotate Device Credentials
					</Label>
					<div class="col-span-3">
						<Skeleton class="h-6 w-16" />
					</div>
				</div>
			</div>

			<DialogFooter>
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="closeDialog('DialogNewCred')" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button v-if="props.editId === 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog()" variant="primary" :disabled="isLoading">
					Save
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">
							Ctrl&nbsp;
							<RcIcon name="enter" class="ml-1" />
						</kbd>
					</div>
				</Button>

				<Button v-if="props.editId > 0" type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog()" variant="primary" :disabled="isLoading">
					Update
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