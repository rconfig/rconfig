import { watch } from "vue";

export function useToggleSort(sortParamRef, fetchFn, storageKey = "defaultSort") {
	// Load saved sort from localStorage on initialization
	const savedSort = localStorage.getItem(`sort_${storageKey}`);
	if (savedSort) {
		sortParamRef.value = savedSort;
	}

	// Watch for changes and save to localStorage
	watch(
		sortParamRef,
		(newSort) => {
			localStorage.setItem(`sort_${storageKey}`, newSort);
		},
		{ immediate: false }
	);

	function toggleSort(field) {
		if (sortParamRef.value === field) {
			sortParamRef.value = `-${field}`;
		} else {
			sortParamRef.value = field;
		}
		if (fetchFn) fetchFn();
	}

	return { toggleSort };
}
