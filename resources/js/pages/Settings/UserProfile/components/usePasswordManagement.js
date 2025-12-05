import { ref, computed } from "vue";
import { useToaster } from "@/composables/useToaster";

export function usePasswordManagement(profileUserId) {
	const { toastSuccess, toastError } = useToaster();
	const isSaving = ref(false);

	const passwords = ref({
		current: "",
		new: "",
		confirm: "",
	});

	const passwordVisibility = ref({
		current: false,
		new: false,
		confirm: false,
	});

	// Computed properties
	const passwordsMatch = computed(() => {
		if (!passwords.value.new && !passwords.value.confirm) return null;
		return passwords.value.new === passwords.value.confirm;
	});

	const canChangePassword = computed(() => {
		return passwords.value.current && passwords.value.new && passwords.value.confirm && passwordsMatch.value;
	});

	async function changePassword() {
		isSaving.value = true;

		if (!passwordsMatch.value) {
			toastError("Password Error", "New password and confirmation do not match");
			return;
		}

		try {
			await axios.post(`/api/user/${profileUserId}/change-password`, {
				current_password: passwords.value.current,
				new_password: passwords.value.new,
				new_password_confirmation: passwords.value.confirm,
			});

			toastSuccess("Password Updated", "Your password has been changed successfully.");

			// Reset password fields
			passwords.value = {
				current: "",
				new: "",
				confirm: "",
			};
		} catch (error) {
			let errorMessage = "Failed to update password";

			if (error.response?.data?.errors) {
				const errors = error.response.data.errors;
				const firstErrorField = Object.keys(errors)[0];
				if (firstErrorField && errors[firstErrorField][0]) {
					errorMessage = errors[firstErrorField][0];
				}
			} else if (error.response?.data?.message) {
				errorMessage = error.response.data.message;
			}

			toastError("Update Failed", errorMessage);
		} finally {
			isSaving.value = false;
		}
	}

	return {
		canChangePassword,
		changePassword,
		passwordVisibility,
		passwords,
		passwordsMatch,
		isSaving,
	};
}
