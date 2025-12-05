import { ref, watch } from "vue";
import { useToaster } from "@/composables/useToaster";

export function usePersonalDetails(profileUserId, user) {
	const { toastSuccess, toastError } = useToaster();
	const isSaving = ref(false);

	const personalDetails = ref({
		name: "",
		username: "",
	});

	// Watch for user changes and update form
	watch(
		user,
		(newUser) => {
			if (newUser) {
				personalDetails.value = {
					name: newUser.name || "",
					username: newUser.username || "",
				};
			}
		},
		{ immediate: true }
	);

	async function savePersonalDetails() {
		isSaving.value = true;
		try {
			await axios.post(`/api/user/update-profile/${profileUserId}`, {
				name: personalDetails.value.name,
				username: personalDetails.value.username,
			});

			toastSuccess("Profile Updated", "Your profile details have been updated successfully.");
		} catch (error) {
			console.error(error);
			toastError("Update Failed", error.response?.data?.message || "Failed to update profile details");
		} finally {
			isSaving.value = false;
		}
	}

	return {
		personalDetails,
		isSaving,
		savePersonalDetails,
	};
}
