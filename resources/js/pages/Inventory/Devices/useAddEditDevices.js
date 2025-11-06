import axios from "axios";
import { ref, onMounted, onUnmounted } from "vue";
import { useToaster } from "@/composables/useToaster";
import { useDialogStore } from "@/stores/dialogActions";

export function useAddEditDevices(editId, emit, isClone) {
	const dialogStore = useDialogStore();
	const isLoading = ref(false);
	const showConfirmCloseAlert = ref(false);
	const { closeDialog, isDialogOpen } = dialogStore;
	const { toastSuccess, toastError } = useToaster();
	const errors = ref([]);

	const model = ref({
		device_name: "",
		device_ip: "",
		device_port_override: "",
		device_cred_id: 0,
		device_cred: [],
		device_vendor: [],
		device_model: "",
		selectedVendorObj: [],
		selectedCategoryObj: [],
		selectedModelObj: [],
		selectedTemplateObj: [],
		device_category_id: 0,
		selectedTagObj: [],
		device_username: "",
		device_password: "",
		device_enable_password: "",
		device_template: [],
		device_main_prompt: "",
		device_enable_prompt: "",
	});

	onMounted(() => {
		if (editId > 0) {
			isLoading.value = true;
			axios.get(`/api/devices/${editId}`).then((response) => {
				// Set core device info
				model.value.device_name = isClone ? `${response.data.device_name}-Clone` : response.data.device_name;
				model.value.device_ip = isClone ? `${response.data.device_ip}-Clone` : response.data.device_ip;
				model.value.device_port_override = response.data.device_port_override;
				model.value.device_model = response.data.device_model;
				model.value.device_category_id = response.data.device_category_id;
				model.value.device_username = response.data.device_username;
				model.value.device_password = response.data.device_password;
				model.value.device_enable_password = response.data.device_enable_password;
				model.value.device_main_prompt = response.data.device_main_prompt;
				model.value.device_enable_prompt = response.data.device_enable_prompt;

				// Set credentials
				model.value.device_cred_id = response.data.device_cred_id;
				model.value.device_cred = response.data.device_cred_name || [];

				model.value.selectedVendorObj = response.data.vendor[0];
				model.value.selectedTemplateObj = response.data.template[0];
				model.value.selectedCategoryObj = response.data.category[0];
				model.value.selectedModelObj = { id: 1, name: response.data.device_model };

				// Set tags
				response.data.tag.forEach((tag) => model.value.selectedTagObj.push(tag));

				// Done
				isLoading.value = false;
			});
		}

		window.addEventListener("keydown", handleKeyDown);
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", handleKeyDown);
	});

	function handleKeyDown(event) {
		if (event.ctrlKey && event.key === "Enter") {
			saveDialog();
		}
	}

	function saveDialog() {
		if (Object.keys(model.value.selectedVendorObj).length > 0) {
			model.value.device_vendor = model.value.selectedVendorObj.id;
		}

		if (Object.keys(model.value.selectedCategoryObj).length > 0) {
			model.value.device_category_id = model.value.selectedCategoryObj.id;
		}

		if (Object.keys(model.value.selectedModelObj).length > 0) {
			model.value.device_model = model.value.selectedModelObj.name;
		}

		if (Object.keys(model.value.selectedTemplateObj).length > 0) {
			model.value.device_template = model.value.selectedTemplateObj.id;
		}

		if (Object.keys(model.value.selectedTagObj).length > 0) {
			model.value.device_tags = model.value.selectedTagObj.map((tag) => tag.id);
		}

		let id = editId > 0 ? `/${editId}` : "";
		let method = editId > 0 ? "patch" : "post";

		if (isClone === true) {
			id = "";
			method = "post";
		}

		axios[method]("/api/devices" + id, model.value)
			.then((response) => {
				emit("save", response.data);
				toastSuccess("Device saved", "The device has been saved successfully.");

				closeDialog("DialogNewDevice");
			})
			.catch((error) => {
				if (error.response && error.response.data) {
					toastError("Error", "There was an error saving the device.");
				}
				// if error 500 toast the error
				if (error.response && error.response.status === 500) {
					toastError("Server Error", error.response.data.message);
				}

				errors.value = error.response?.data?.errors || [];
			});
	}

	function generatePrompts() {
		model.value.device_main_prompt = model.value.device_name + "#";
		model.value.device_enable_prompt = model.value.device_name + ">";
	}

	function showConfirmCloseDialog() {
		showConfirmCloseAlert.value = true;
	}

	function cancelCloseDialog() {
		showConfirmCloseAlert.value = false;
	}

	function confirmCloseDialog() {
		showConfirmCloseAlert.value = false;
		emit("close");
		closeDialog("DialogNewDevice");
	}

	return {
		// state
		errors,
		isLoading,
		model,
		showConfirmCloseAlert,

		//  methods
		cancelCloseDialog,
		confirmCloseDialog,
		generatePrompts,
		isDialogOpen,
		saveDialog,
		showConfirmCloseDialog,
	};
}