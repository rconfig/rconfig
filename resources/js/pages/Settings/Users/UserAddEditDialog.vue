<script setup>
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import axios from "axios";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onMounted, onUnmounted, watch, computed } from "vue";
import { useDialogStore } from "@/stores/dialogActions";
import { useToaster } from "@/composables/useToaster";

// Component setup
const { toastSuccess, toastError } = useToaster();
const dialogStore = useDialogStore();
const { closeDialog, isDialogOpen } = dialogStore;

// Emits
const emit = defineEmits(["save", "close"]);

// Props
const props = defineProps({
	editId: {
		type: Number,
		default: 0,
	},
});

// Reactive state
const errors = ref({});
const isSaving = ref(false);
const successMessage = ref("");
const formKey = ref(Date.now());
const model = ref({
	get_notifications: 0,
	name: "",
	username: "",
	email: "",
	password: "",
	repeat_password: "",
	role: "",
});

const roles = ref([
	{ id: "Admin", name: "Admin" },
	{ id: "User", name: "User" },
]);

// Computed property for role string binding
const roleIdString = computed({
	get: () => model.value.role ? String(model.value.role) : "",
	set: (value) => {
		model.value.role = value;
	}
});

// Event handlers
function handleKeyDown(event) {
	if (event.ctrlKey && event.key === "Enter") {
		saveDialog();
	}
}

function saveDialog() {
	isSaving.value = true;
	let id = props.editId > 0 ? `/${props.editId}` : "";
	let method = props.editId > 0 ? "patch" : "post";

	axios[method]("/api/users" + id, model.value)
		.then((response) => {
			isSaving.value = false;
			emit("save", response.data);
			toastSuccess(props.editId > 0 ? "User updated" : "User created", props.editId > 0 ? "The user has been updated successfully." : "The user has been created successfully.");

			resetForm();
			closeDialog("DialogNewUser");
		})
		.catch((error) => {
			isSaving.value = false;
			errors.value = error.response.data.errors;
			toastError("Error", "There was an error saving the user. Please check the form for errors.");
		});
}

function setRole(role) {
	model.value.role = role;
}

function onDialogClose() {
	closeDialog("DialogNewUser");

	// Reset form when closing if not editing
	if (props.editId === 0) {
		resetForm();
	}
}

function resetForm() {
	model.value = {
		get_notifications: 0,
		name: "",
		username: "",
		email: "",
		password: "",
		repeat_password: "",
		role: "",
	};
	errors.value = {};
	formKey.value = Date.now();
}

// Lifecycle hooks
onMounted(() => {
	if (props.editId > 0) {
		axios
			.get(`/api/users/${props.editId}`)
			.then((response) => {
				model.value = response.data;
			})
			.catch((error) => {
				isSaving.value = false;
				errors.value = error.response?.data?.errors || {};
				toastError("Error", error.response?.data?.message || "Failed to load user data");
			});
	}

	window.addEventListener("keydown", handleKeyDown);
});

onUnmounted(() => {
	window.removeEventListener("keydown", handleKeyDown);
});

// Watchers
watch(
	() => [model.value.password, model.value.repeat_password],
	() => {
		if (model.value.password && model.value.repeat_password) {
			if (model.value.password !== model.value.repeat_password) {
				successMessage.value = "";
				errors.value.repeat_password = ["Passwords do not match"];
			} else {
				delete errors.value.repeat_password;
				successMessage.value = "Passwords match";
			}
		}
	}
);
</script>

<template>
	<Dialog :open="isDialogOpen('DialogNewUser')">
		<DialogTrigger as-child>
			<!-- Trigger content would go here if needed -->
		</DialogTrigger>
		<DialogContent class="p-0 sm:max-w-fit" :onEscapeKeyDown="onDialogClose" :onInteractOutside="onDialogClose" :onCloseAutoFocus="onDialogClose">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-rcgray-200">
					<div class="flex items-center">
						<RcIcon name="user" class="w-5 h-5" />
						<span class="ml-2">
							{{ editId > 0 ? "Edit" : "Add" }} User
							{{ editId > 0 ? "(ID: " + editId + ")" : "" }}
						</span>
					</div>
				</DialogTitle>
			</DialogHeader>

			<form :key="formKey" autocomplete="off" @submit.prevent="saveDialog" class="grid gap-2 p-4" novalidate>
				<!-- Hidden fields to trick browsers -->
				<input type="text" style="display: none;" name="fakeusernameremembered" />
				<input type="password" style="display: none;" name="fakepasswordremembered" />

				<!-- Name -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="name" class="text-right">
						Name
						<span class="text-red-400">*</span>
					</Label>
					<Input v-model="model.name" id="name" class="col-span-3" autocomplete="new-name" name="new-name" />
					<span v-if="errors?.name?.[0]">{{ errors.name[0] }}</span>
				</div>

				<!-- Username -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="username" class="text-right">Username</Label>
					<Input v-model="model.username" id="username" class="col-span-3" autocomplete="new-username" name="new-username" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.username?.[0]">
						{{ errors.username[0] }}
					</span>
				</div>

				<!-- Email -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="email-address" class="text-right">
						Email
						<span class="text-red-400">*</span>
					</Label>
					<Input v-model="model.email" id="email-address" name="email-address" type="email" class="col-span-3" autocomplete="new-email" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.email?.[0]">
						{{ errors.email[0] }}
					</span>
				</div>

				<!-- Password -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="new-password" class="text-right">
						Password
						<span class="text-red-400" v-if="editId === 0">*</span>
					</Label>
					<Input type="password" v-model="model.password" id="new-password" name="new-password" class="col-span-3" autocomplete="new-password" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.password?.[0]">
						{{ errors.password[0] }}
					</span>
				</div>

				<!-- Confirm Password -->
				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="confirm-new-password" class="text-right">
						Confirm Password
						<span class="text-red-400" v-if="editId === 0">*</span>
					</Label>
					<Input type="password" v-model="model.repeat_password" id="confirm-new-password" name="confirm-new-password" class="col-span-3" autocomplete="new-password" />
					<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.repeat_password?.[0]">
						{{ errors.repeat_password[0] }}
					</span>
					<span class="col-span-3 col-start-2 text-sm text-green-400" v-if="successMessage && model.password && model.repeat_password">
						{{ successMessage }}
					</span>
				</div>

				<div class="grid items-center grid-cols-4 gap-2">
					<Label for="role-select" class="text-right">
						Role
						<span class="text-red-400">*</span>
					</Label>
				<Select v-model="roleIdString">
					<SelectTrigger id="role-select" class="col-span-3">
						<SelectValue placeholder="Select a role" />
					</SelectTrigger>
					<SelectContent>
						<SelectGroup>
							<SelectItem v-for="role in roles" :key="role.id" :value="String(role.id)" @click="setRole(role.id)">
								{{ role.name }}
							</SelectItem>
						</SelectGroup>
					</SelectContent>
				</Select>
				<span class="col-span-3 col-start-2 text-sm text-red-400" v-if="errors?.role?.[0]">
					{{ errors.role[0] }}
				</span>
				</div>

				<!-- Notifications -->
				<div class="grid items-center grid-cols-4 gap-2 mt-2">
					<Label for="get-notifications" class="text-right">
						Notifications
					</Label>
					<div class="flex items-center col-span-3">
						<Switch id="get-notifications" v-model="model.get_notifications" :checked="model.get_notifications === 1" @update:checked="model.get_notifications = $event ? 1 : 0" />
						<Label for="get-notifications" class="ml-2">
							{{ model.get_notifications === 1 ? "Enabled" : "Disabled" }}
						</Label>
					</div>
				</div>
			</form>

			<DialogFooter class="rc-dialog-footer bg-rcgray-800">
				<Button type="close" variant="outline" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse" @click="onDialogClose" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class">ESC</kbd>
					</div>
				</Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click="saveDialog()" variant="primary">
					<Spinner :state="isSaving" class="mr-2" />
					{{ props.editId > 0 ? "Update" : "Save" }}
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