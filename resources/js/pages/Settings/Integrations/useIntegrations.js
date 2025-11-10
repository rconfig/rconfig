import { ref, markRaw } from "vue";
import MSLogo from "@/pages/Shared/Icon/Icons/MSLogo.vue";
import OktaLogo from "@/pages/Shared/Icon/Icons/OktaLogo.vue";
import GoogleLogo from "@/pages/Shared/Icon/Icons/GoogleLogo.vue";
import PassboltLogo from "@/pages/Shared/Icon/Icons/PassboltLogo.vue";
import StatusGreenIcon from "@/pages/Shared/Icon/Icons/StatusGreenIcon.vue";
import StatusGrayIcon from "@/pages/Shared/Icon/Icons/StatusGrayIcon.vue";

export function useIntegrations() {
	const isLoading = ref(false);
	const integrations = ref([]);
	const ssoIntegrations = ref([]);
	const configuredIntegrations = ref([]);

	// Create a component map to reference icons by name
	const iconComponents = {
		MSLogo: markRaw(MSLogo),
		OktaLogo: markRaw(OktaLogo),
		GoogleLogo: markRaw(GoogleLogo),
		PassboltLogo: markRaw(PassboltLogo),
	};

	const statusIcons = {
		StatusGreenIcon: markRaw(StatusGreenIcon),
		StatusGrayIcon: markRaw(StatusGrayIcon),
	};

	// Function to get configured integrations from the API
	function getConfiguredIntegrations() {
		isLoading.value = true;
		return axios
			.get("/api/integrations/configured/")
			.then((response) => {
				configuredIntegrations.value = response.data;
				isLoading.value = false;
			})
			.catch((error) => {
				console.log(error);
				isLoading.value = false;
			});
	}

	// Function to get integration options from the API
	function getIntegrationOptions() {
		isLoading.value = true;
		return axios
			.get("/api/integrations/options")
			.then((response) => {
				integrations.value = response.data.data;
				//only Active integrations
				integrations.value = integrations.value.filter((integration) => integration.status === "Active");

				// Transform data to ensure icon property matches component name
				integrations.value = integrations.value.map((integration) => {
					// Ensure the icon property exists and matches a component name
					if (!integration.icon || !iconComponents[integration.icon]) {
						console.warn(`Icon component '${integration.icon}' not found for integration: ${integration.name}`);
						// Use a default if needed
						integration.iconComponent = null;
					} else {
						integration.iconComponent = iconComponents[integration.icon];
					}
					return integration;
				});

				ssoIntegrations.value = integrations.value.filter((integration) => integration.type === "SSO");
				isLoading.value = false;
			})
			.catch((error) => {
				console.error("Failed to fetch integrations:", error);
				isLoading.value = false;
			});
	}

	// Function to determine if an SSO integration is configured
	function isIntegrationConfigured(integration) {
		// Check if configuredIntegrations.value and auth_services exist
		if (!configuredIntegrations.value || !configuredIntegrations.value.auth_services) {
			return false;
		}

		// Map integration names to their respective keys in auth_services
		const nameToKeyMap = {
			Okta: "okta",
			Microsoft: "microsoft",
			Google: "google",
			SAML2: "saml2",
		};

		// Find the service key based on integration name
		let serviceKey = null;
		for (const [key, value] of Object.entries(nameToKeyMap)) {
			if (integration.name.includes(key)) {
				serviceKey = value;
				break;
			}
		}

		// If no matching key was found, return false
		if (!serviceKey || !configuredIntegrations.value.auth_services[serviceKey]) {
			return false;
		}

		// Return whether the integration is configured
		return configuredIntegrations.value.auth_services[serviceKey].is_configured === true;
	}


	// Function to open configuration link
	function openLink(integration) {
		if (integration.external_url === 1) {
			window.open(integration.config_url, "_blank");
		} else {
			window.location.href = integration.config_url;
		}
	}

	// Initialize the integrations data
	function initializeIntegrations() {
		return Promise.all([getIntegrationOptions(), getConfiguredIntegrations()]);
	}

	return {
		isLoading,
		integrations,
		ssoIntegrations,
		configuredIntegrations,
		statusIcons,
		getConfiguredIntegrations,
		getIntegrationOptions,
		isIntegrationConfigured,
		openLink,
		initializeIntegrations,
	};
}
