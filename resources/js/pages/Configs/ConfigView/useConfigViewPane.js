import axios from "axios";
import { ref, onMounted, onUnmounted } from "vue";
import { usePanelStore } from "@/stores/panelStore";
import { useToaster } from "@/composables/useToaster";

export function useConfigViewPane(props, emit) {
	const configData = ref(null);
	const isLoading = ref(false);
	const panelElement2 = ref(null);
	const leftNavSelected = ref("details");
	const panelStore = usePanelStore();
	const { toastError } = useToaster();

	onMounted(() => {
		fetchConfig();

		panelStore.panelRef2 = panelElement2.value;

		window.addEventListener("keydown", (e) => {
			if (e.key === "Escape") {
				onEsc();
			}
		});
	});

	onUnmounted(() => {
		window.removeEventListener("keydown", (e) => {
			if (e.key === "Escape") {
				onEsc();
			}
		});
	});

	function fetchConfig() {
		axios
			.get(`/api/configs/${props.configId}`)
			.then((response) => {
				configData.value = response.data;
				isLoading.value = false;
			})
			.catch((error) => {
				console.error(error);
				toastError("Error", "Something went wrong - could not retrieve the configuration from the file system!");
			});
	}

	function close() {
		emit("close");
	}

	function onEsc() {
		close();
	}

	function closeNav() {
		panelElement2?.value.isCollapsed ? panelElement2?.value.expand() : panelElement2?.value.collapse();
	}

	function selectLeftNavView(navItem) {
		leftNavSelected.value = navItem;
	}

	return {
		configData,
		isLoading,
		panelElement2,
		leftNavSelected,
		closeNav,
		selectLeftNavView,
	};
}
