import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { useToaster } from "@/composables/useToaster";
import jsYaml from "js-yaml";

export default function useTemplateAddEdit(props, emit) {
	const { toastSuccess, toastError } = useToaster();
	const errors = ref([]);
	const code = ref("");
	const isLoading = ref(false);
	const model = ref({
		cm_lock: false,
		code: "",
		templateName: "",
		description: "",
	});

	function getDefaultTemplate(meditor) {
		axios
			.get("/api/get-default-template")
			.then((response) => {
				code.value = response.data;
				meditor.setValue(response.data);

				// parse yml to array and set template name and description
				const parsedYaml = parseYaml(response.data);
				model.value.templateName = parsedYaml.main?.name || "Default Template";
				model.value.description = parsedYaml.main?.desc || parsedYaml.main?.description || "This is the default template for inventory items.";
			})
			.catch((error) => {
				meditor.updateOptions({
					value: "Something went wrong - could not retrieve the default template from the file system!",
				});
				toastError("Error", "Something went wrong - could not retrieve the default template from the file system!");
			});
	}

	function parseYaml(yamlString) {
		try {
			const result = jsYaml.load(yamlString);
			return result;
		} catch (e) {
			console.error("Error parsing YAML:", e);
			return {};
		}
	}

	function reformatTemplateCode(meditor) {
		axios
			.post("/api/reformat-template", {
				fileName: model.fileName,
			})
			.then((response) => {
				code.value = response.data;
				meditor.setValue(response.data);
			})
			.catch((error) => {
				console.log(error);
				toastError("Error", "Something went wrong - " + error.response?.data?.message || "could not reformat the template code!");
			});
	}

	function showTemplate(editId, meditor, model) {
		isLoading.value = true;
		axios
			.get("/api/templates/" + editId)
			.then((response) => {
				// handle success
				model.fileName = response.data.fileName;
				code.value = response.data.code;
				meditor.getModel().setValue(response.data.code);
				isLoading.value = false;
			})
			.catch((error) => {
				meditor.updateOptions({
					value: "Something went wrong - could not retrieve the template from the file system!",
				});
				toastError("Error", "Something went wrong - could not retrieve the template from the file system!");
			});
	}

	function saveDialog(editId, model, meditor, emit, close) {
		model.value.code = meditor.getValue();

		let id = editId > 0 ? `/${editId}` : ""; // determine if we are creating or updating
		let method = editId > 0 ? "patch" : "post"; // determine if we are creating or updating

		axios[method]("/api/templates" + id, {
			templateName: model.value.templateName,
			description: model.value.description,
			code: model.value.code,
			fileName: model.value.fileName,
		})
			.then((response) => {
				emit("save", response.data);
				toastSuccess("Template saved", "The template has been saved successfully.");
				close();
			})
			.catch((error) => {
				errors.value = error.response.data.errors;
				toastError("Error", "There was an error saving the template. " + (error.response?.data?.message || ""));
				console.error("Error saving template:", error);
			});
	}

	function handleKeyDown(event, saveFunction) {
		if (event.ctrlKey && event.key === "Enter") {
			saveFunction();
		}
	}

	function fetchTemplateData(editId) {
		if (editId > 0) {
			axios.get(`/api/templates/${editId}`).then((response) => {
				model.value = response.data;
			});
		}
	}

	onMounted(() => {
		fetchTemplateData(props.editId);
		window.addEventListener("keydown", (event) => handleKeyDown(event, () => saveDialog(props.editId, model, null, emit, () => emit("close"))));
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", handleKeyDown);
	});

	return {
		code,
		errors,
		isLoading,
		fetchTemplateData,
		getDefaultTemplate,
		handleKeyDown,
		model,
		parseYaml,
		reformatTemplateCode,
		saveDialog,
		showTemplate,
	};
}
