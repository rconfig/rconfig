<script setup>
import axios from "axios";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { eventBus } from "@/composables/eventBus";
import { onMounted, ref, watch, computed } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";

const emit = defineEmits(["close", "save"]);
const props = defineProps({
	editId: {
		type: Number,
		required: false,
		default: 0,
	},
	vaultStatus: {
		type: String,
		required: true,
	},
});

const { toastSuccess, toastError } = useToaster();
const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;

const formData = ref({
	id: 0,
	cred_name: "",
	cred_description: "",
	vault_endpoint: "",
	device_username: "device_username",
	device_password: "device_password",
	device_enable_password: "device_enable_password",
	cred_is_default: false,
	vault_enabled: true,
	vault_integration_id: 1,
});
const errors = ref({});
const isSubmitting = ref(false);
const errorMessage = ref("");
const showDialog = ref(true);

const typetext = computed(() => (props.editId ? "Edit" : "Add"));

// Helper text for vault endpoint based on vault status
function endpointHelperText() {
	if (props.vaultStatus === "hashicorp") return "For hashicorp e.g. /kvengine/data/secret";
	return "Enter the vault endpoint path";
}

async function loadCredential() {
	try {
		const response = await axios.get(`/api/settings/credentials/${props.editId}`);
		const data = response.data.data;

		// Map the response data to our form fields
		formData.value.id = data.id;
		formData.value.cred_name = data.cred_name;
		formData.value.cred_description = data.cred_description;
		formData.value.cred_is_default = data.cred_is_default;
		formData.value.vault_enabled = data.vault_enabled;
		formData.value.vault_integration_id = data.vault_integration_id;
		formData.value.vault_endpoint = data.vault_endpoint;

		// Handle the vault_creds_mapping if it exists
		if (data.vault_creds_mapping && data.vault_creds_mapping.length > 0) {
			formData.value.device_username = data.vault_creds_mapping[0].device_username || "device_username";
			formData.value.device_password = data.vault_creds_mapping[0].device_password || "device_password";
			formData.value.device_enable_password = data.vault_creds_mapping[0].device_enable_password || "device_enable_password";
		}
	} catch (error) {
		console.error("Error loading credential:", error);
		toastError("Error", "Failed to load credential details");
	}
}

onMounted(() => {
	if (props.editId) {
		loadCredential();
	}
});

function saveDialog() {
	let id = props.editId > 0 ? `/${props.editId}` : ""; // determine if we are creating or updating
	let method = props.editId > 0 ? "patch" : "post"; // determine if we are creating or updating
	axios[method]("/api/settings/credentials" + id, {
		vault_enabled: formData.value.vault_enabled,
		cred_name: formData.value.cred_name,
		cred_description: formData.value.cred_description,
		cred_username: formData.value.cred_username,
		cred_password: formData.value.cred_password,
		cred_enable_password: formData.value.cred_enable_password,
		cred_is_default: formData.value.cred_is_default,
		vault_integration_id: formData.value.vault_integration_id,
		vault_endpoint: formData.value.vault_endpoint,
		vault_creds_mapping: [
			{
				device_username: formData.value.device_username,
				device_password: formData.value.device_password,
				device_enable_password: formData.value.device_enable_password,
			},
		],
		vaultStatusName: props.vaultStatus,
	})
		.then((response) => {
			closeDialog("DialogNewVaultCred");
			emit("save", response.data);
			toastSuccess("Credential created", "The credential has been created successfully.");
		})
		.catch((error) => {
			console.error("Error creating credential:", error);
			errors.value = error.response.data.errors;
			toastError("Error", "Failed to create credential");
		});
}

function handleClose() {
	closeDialog("DialogNewVaultCred");
	emit("close");
}
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewVaultCred')" @close="handleClose">
		<DialogContent class="sm:max-w-[800px]" @escapeKeyDown="closeDialog('DialogNewVaultCred')" @pointerDownOutside="closeDialog('DialogNewVaultCred')" @closeClicked="closeDialog('DialogNewVaultCred')">
			<DialogHeader>
				<DialogTitle>
					<div class="flex items-center">
						<RcIcon name="vault" class="mr-2 text-yellow-300" />
						{{ typetext }} Vault Credential - {{ vaultStatus }}
					</div>
				</DialogTitle>
				<DialogDescription>
					Create vault-based credentials for secure device access
				</DialogDescription>
			</DialogHeader>

			<form @submit.prevent="saveDialog" class="space-y-6">
				<!-- Basic Information Section -->
				<div class="grid gap-2 py-4 space-y-4">
					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="cred_name" class="text-right">
							Credential Name
							<span class="text-red-400">*</span>
						</Label>
						<Input id="cred_name" v-model="formData.cred_name" class="col-span-3" autocomplete="off" placeholder="Enter credential name" />
						<p v-if="errors.cred_name" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.cred_name[0] }}</p>
					</div>

					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="cred_description" class="text-right">
							Description
						</Label>
						<Input v-model="formData.cred_description" id="cred_description" class="col-span-3" autocomplete="off" placeholder="Enter credential description" />
						<p v-if="errors.cred_description" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.cred_description[0] }}</p>
					</div>

					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="vault_endpoint" class="text-right">
							Vault Endpoint
							<span class="text-red-400">*</span>
						</Label>
						<Input v-model="formData.vault_endpoint" id="vault_endpoint" class="col-span-3" autocomplete="off" placeholder="Enter vault endpoint" />
						<small class="text-xs text-muted-foreground col-span-3 col-start-2 ml-2 mt-1">{{ endpointHelperText() }}</small>
						<p v-if="errors.vault_endpoint" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.vault_endpoint[0] }}</p>
					</div>
				</div>

				<!-- Field Mapping Section -->
				<div class="space-y-4 pt-4 border-t">
					<h3 class="text-lg font-medium">Field Mapping</h3>
					<p class="text-sm text-muted-foreground">Map your Secret Keys below if they differ from the default rConfig keys ('device_username', 'device_password', 'device_enable_password'). Adjust as necessary.</p>

					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="device_username" class="text-right">
							Username Key
							<span class="text-red-400">*</span>
						</Label>
						<Input v-model="formData.device_username" id="device_username" class="col-span-3" autocomplete="off" placeholder="device_username key" />
						<p v-if="errors.device_username" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.device_username[0] }}</p>
					</div>

					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="device_password" class="text-right">
							Password Key
							<span class="text-red-400">*</span>
						</Label>
						<Input v-model="formData.device_password" id="device_password" class="col-span-3" autocomplete="off" placeholder="device_password key" />
						<p v-if="errors.device_password" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.device_password[0] }}</p>
					</div>

					<div class="grid items-center grid-cols-4 gap-x-4">
						<Label for="device_enable_password" class="text-right">
							Enable Password Key
							<span class="text-red-400">*</span>
						</Label>
						<Input v-model="formData.device_enable_password" id="device_enable_password" class="col-span-3" autocomplete="off" placeholder="device_enable_password key" />
						<p v-if="errors.device_enable_password" class="text-red-500 text-sm col-start-2 ml-2 col-span-3">{{ errors.device_enable_password[0] }}</p>
					</div>
				</div>

				<div v-if="errorMessage" class="text-red-500 text-sm col-start-2 ml-2 col-span-3 mt-2">
					{{ errorMessage }}
				</div>

				<DialogFooter>
					<Button type="button" variant="outline" @click="handleClose">Cancel</Button>
					<Button type="submit" :disabled="isSubmitting">
						<div v-if="isSubmitting" class="flex items-center">
							<Spinner class="w-4 h-4 mr-2" />
							Saving...
						</div>
						<span v-else>{{ typetext }}</span>
					</Button>
				</DialogFooter>
			</form>
		</DialogContent>
	</Dialog>
</template>
