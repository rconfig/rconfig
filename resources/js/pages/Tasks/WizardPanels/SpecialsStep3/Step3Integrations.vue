<script setup>
import axios from "axios";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { onMounted, ref, watch } from "vue";
import { useToaster } from "@/composables/useToaster";
import { Info } from "lucide-vue-next";

const { toastError } = useToaster();

const props = defineProps({
	model: {
		type: Object,
		required: true,
	},
});

const integrations = ref([]);
const selectedIntegration = ref(null);
const isLoading = ref(true);

onMounted(async () => {
	await getConfiguredIntegrations();

	// Set selected integration if model has one
	if (props.model.integration_id) {
		selectedIntegration.value = integrations.value.find((integration) => integration.id === props.model.integration_id);
	}
});

// Watch for integration selection changes
watch(selectedIntegration, (newIntegration) => {
	if (newIntegration) {
		// Set task command based on integration type
		if (newIntegration.integration_option_id === 2) {
			props.model.task_command = "rconfig:integration-zabbix -d";
		}
		if (newIntegration.integration_option_id === 4) {
			props.model.task_command = "rconfig:integration-netbox -d";
		}
		props.model.integration_id = newIntegration.id;
	}
});

async function getConfiguredIntegrations() {
	try {
		isLoading.value = true;
		const response = await axios.get("/api/integrations/configured");
		integrations.value = response.data.integrations.data || [];
	} catch (error) {
		toastError("Error loading integrations", error.response?.data?.message || "Failed to load integrations");
	} finally {
		isLoading.value = false;
	}
}
</script>

<template>
	<div class="space-y-4">
		<div class="space-y-2">
			<Label for="integration-select" class="block">
				Select from configured integrations
				<span class="text-red-500 ml-1">*</span>
			</Label>

			<!-- Loading state -->
			<div v-if="isLoading" class="flex items-center justify-center py-8">
				<div class="flex items-center space-x-2 text-muted-foreground">
					<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
					<span class="text-sm">Loading integrations...</span>
				</div>
			</div>

			<!-- Empty state - No integrations configured -->
			<div v-else-if="integrations.length === 0" class="flex flex-col items-center justify-center py-8 px-4 text-center border-2 border-dashed border-muted-foreground/20 rounded-lg">
				<div class="w-12 h-12 mb-4 rounded-full bg-muted/50 flex items-center justify-center">
					<RcIcon name="integrations" class="w-6 h-6 text-muted-foreground" />
				</div>

				<h3 class="text-lg font-semibold text-foreground mb-2">
					Empty state
				</h3>

				<p class="text-muted-foreground text-sm max-w-sm leading-relaxed mb-4">
					There are no integrations configured.
					<router-link to="/settings/integrations" class="text-primary hover:text-primary/80 underline font-medium">
						Create an Integration
					</router-link>
					to continue
				</p>

				<div class="mt-2 p-3 bg-muted/30 rounded-lg border border-dashed border-muted-foreground/20">
					<div class="flex items-center justify-center space-x-2 text-xs text-muted-foreground">
						<Info class="w-3 h-3" />
						<span>Configure integrations in Settings</span>
					</div>
				</div>
			</div>

			<!-- Integration selector -->
			<div v-else>
				<Select v-model="selectedIntegration" id="integration-select">
					<SelectTrigger class="w-full">
						<SelectValue placeholder="-- Select an integration --" />
					</SelectTrigger>
					<SelectContent>
						<SelectItem v-for="integration in integrations" :key="integration.id" :value="integration">
							<div class="flex items-center space-x-2">
								<RcIcon name="integrations" class="w-4 h-4 text-muted-foreground" />
								<span>{{ integration.name }}</span>
							</div>
						</SelectItem>
					</SelectContent>
				</Select>

				<p class="text-sm text-muted-foreground mt-1">
					Choose an integration to configure the task
				</p>
			</div>
		</div>

		<!-- Selected integration info -->
		<div v-if="selectedIntegration" class="mt-4 p-3 bg-muted/50 rounded-lg border">
			<div class="flex items-center space-x-2 text-sm">
				<RcIcon name="check" class="w-4 h-4 text-green-600" />
				<span class="font-medium">Selected:</span>
				<span class="text-muted-foreground">{{ selectedIntegration.name }}</span>
			</div>
		</div>
	</div>
</template>
