import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

export function useUserProfile(profileUserId, loggedInUserId) {
	const router = useRouter();
	const user = ref(null);
	const currentUser = ref(null);
	const isLoading = ref(true);
	const error = ref(null);
	const accessStatus = ref("loading"); // 'loading', 'authorized', 'unauthorized', 'invalid'

	const isAuthorized = computed(() => accessStatus.value === "authorized");
	const isUnauthorized = computed(() => accessStatus.value === "unauthorized");
	const isInvalid = computed(() => accessStatus.value === "invalid");

	// Initialize
	onMounted(() => {
		checkAuthorization();

		fetchUserProfile();
	});

	// Fetch the user profile data
	const fetchUserProfile = async () => {
		try {
			isLoading.value = true;
			error.value = null;

			// Only proceed with fetching the profile if authorized
			const response = await axios.get(`/api/users/${profileUserId}`);
			user.value = response.data;
		} catch (err) {
			console.log(err);
			error.value = err.response?.data?.message || "Failed to load user profile";
			accessStatus.value = "invalid";
		} finally {
			isLoading.value = false;
		}
	};

	// Check if current user can access the requested profile
	const checkAuthorization = () => {
		if (!loggedInUserId || !profileUserId) {
			accessStatus.value = "invalid";
			return false;
		}

		const requestedId = parseInt(profileUserId, 10);
		const currentId = parseInt(loggedInUserId, 10);

		if (currentId === requestedId) {
			accessStatus.value = "authorized";
			return true;
		} else {
			accessStatus.value = "unauthorized";
			return false;
		}
	};

	return {
		user,
		currentUser,
		isLoading,
		error,
		accessStatus,
		isAuthorized,
		isUnauthorized,
		isInvalid,
		fetchUserProfile,
	};
}
