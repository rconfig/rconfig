import { ref } from "vue";
import { useClipboard } from "@vueuse/core";

export function useCopy() {
	const { text, copy, copied, isSupported } = useClipboard();
	const activeCopyIcon = ref({});

	const copyItem = async (key, value) => {
		try {
			copy(value);
			activeCopyIcon.value[key] = true;
			setTimeout(() => {
				activeCopyIcon.value[key] = false;
			}, 1500);
		} catch (e) {
			console.error("Failed to copy:", e);
		}
	};

	return {
		copyItem,
		activeCopyIcon,
	};
}
