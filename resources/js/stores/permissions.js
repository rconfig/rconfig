import { defineStore } from "pinia";
import axios from "axios";

const SESSION_KEY = "session-id";
const PERSIST_KEY = "pinia-permissions"; // default Pinia key unless you override it

export const usePermissionsStore = defineStore("permissions", {
	state: () => ({
		permissions: [],
		lastLoaded: null,
		isLoading: false,
	}),

	getters: {
		permissionsCount(state) {
			return state.permissions.length;
		},
	},

	actions: {
		async loadPermissions(userId) {
			const sessionId = sessionStorage.getItem(SESSION_KEY);

			if (this.permissions.length && this.lastLoaded === sessionId) return;

			this.isLoading = true;
			try {
				const { data } = await axios.get(`/api/users/permissions/${userId}`);
				this.permissions = data.permissions;
				this.lastLoaded = sessionId;
			} catch (e) {
				console.error("Failed to load permissions", e);
			} finally {
				this.isLoading = false;
			}
		},

		async forceReloadPermissions(userId) {
			this.isLoading = true;
			try {
				const { data } = await axios.get(`/api/users/permissions/${userId}`);
				this.permissions = data.permissions;
				this.lastLoaded = sessionStorage.getItem(SESSION_KEY);
			} catch (e) {
				console.error("Failed to force reload permissions", e);
			} finally {
				this.isLoading = false;
			}
		},

		clearPermissions() {
			this.permissions = [];
			this.lastLoaded = null;
		},

		resetOnLogout() {
			this.$reset(); // Reset Pinia state
			sessionStorage.removeItem(PERSIST_KEY); // Remove persisted store state
			sessionStorage.removeItem(SESSION_KEY); // Optional: remove session ID
		},
	},

	persist: {
		storage: sessionStorage,
	}, // key defaults to `pinia-permissions`
});
