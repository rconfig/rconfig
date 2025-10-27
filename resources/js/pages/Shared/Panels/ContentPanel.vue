<script setup>
import AgentViewPane from "@/pages/Settings/Agents/AgentsView/AgentViewPane.vue";
import DeviceViewPane from "@/pages/Inventory/Devices/DeviceView/DeviceViewPane.vue";
import ApiViewPane from "@/pages/Inventory/ApiCollections/ApiView/ApiViewPane.vue";
import ConfigViewPane from "@/pages/Configs/ConfigView/ConfigViewPane.vue";
import ChangePulseViewPane from "@/pages/Configs/ChangeManager/ChangePulse/ViewDetails/ChangePulseViewPane.vue";
import TemplateAddEditPane from "@/pages/Inventory/Templates/TemplateAddEditPane.vue";
import RoleAddEditPane from "@/pages/Settings/Rbac/RoleAddEditPane.vue";
import PolicyViewPane from "@/pages/Compliance/ComplianceDefinitions/PolicyDefinitionDetails/PolicyViewPane.vue";
import PolicyDefinitionAddEditPane from "@/pages/Compliance/ComplianceDefinitions/PolicyDefinitionAddEditPane.vue";
import PolicyReportsViewPanel from "@/pages/Compliance/ComplianceReports/PolicyReportDetails/PolicyReportsViewPanel.vue";
import CicDefinitionAddEditPane from "@/pages/Compliance/CicDefinitions/CicDefinitionAddEditPane.vue";
import PolicyHelpDocument from "@/pages/Compliance/Help/PolicyHelpDocument.vue";
import PolicyResultsDetailsPane from "@/pages/Compliance/ComplianceReports/PolicyResults/PolicyResultsDetailsPane.vue";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router"; // Import the useRoute from Vue Router
import ContentPanelI18n from "@/i18n/pages/Shared/Panels/ContentPanel.i18n.js";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const route = useRoute();
const router = useRouter();
const emit = defineEmits(["close"]);
const panelId = ref(0);
const panelContentName = ref(null);
const referringPage = ref(null);

const props = defineProps({});
const { t } = useComponentTranslations(ContentPanelI18n);

onMounted(() => {
	panelId.value = parseInt(route.params.id, 10);
	panelContentName.value = route.name;
	referringPage.value = route.query.ref || null;
});

function closeDeviceViewPanel() {
	panelContentName.value = null;
	router.push({ name: "devices" });
}

function closeApiViewPanel() {
	panelContentName.value = null;
	router.push({ name: "api-collections" });
}

function closeTemplateViewPanel() {
	panelContentName.value = null;
	router.push({ name: "templates" });
}

function closeRoleViewPanel() {
	panelContentName.value = null;
	router.push({ name: "roles" });
}

function closeConfigViewPanel() {
	// just go back
	router.back();
}

function closeAgentViewPanel() {
	panelContentName.value = null;
	if (referringPage.value) {
		// Navigate back to the referring page with or without the ID
		const navigationParams = route.query.refId ? { name: route.query.ref, params: { id: route.query.refId } } : { name: route.query.ref };

		// Navigate back to the referring page with or without the ID
		router.push(navigationParams);
	} else {
		// Default fallback, if no referring page is set
		router.push({ name: "agents" });
	}
}

function closeChangePulseViewPanel() {
	panelContentName.value = null;
	router.push({ name: "changepulse" });
}

function closePolicyViewPane() {
	panelContentName.value = null;
	router.push({ name: "policy-definitions" });
}

function closePolicyDefinitionPanel() {
	panelContentName.value = null;
	router.push({ name: "policy-definitions" });
}

function closePolicyReportsViewPanel() {
	panelContentName.value = null;
	router.push({ name: "compliance" });
}

function closeCicDefinitionPanel() {
	panelContentName.value = null;
	router.push({ name: "cic-definitions" });
}

function closePolicyHelpPanel(id) {
	// navigate back
	if (referringPage.value) {
		// Navigate back to the referring page with or without the ID
		const navigationParams = route.query.refId ? { name: route.query.ref, params: { id: route.query.refId } } : { name: route.query.ref };

		// Navigate back to the referring page with or without the ID
		router.push(navigationParams);
	} else {
		// Default fallback, if no referring page is set
		router.push({ name: "policy-definitions" });
	}
	panelContentName.value = null;
	panelId.value = id; // Set the ID to the one passed in
	referringPage.value = null; // Reset the referring page
}
</script>

<template>
	<div class="w-screen h-[calc(100vh-72px)] border" style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden;">
		<transition name="fade">
			<DeviceViewPane v-if="panelContentName === 'device-view'" :editId="panelId" @close="closeDeviceViewPanel()" />
		</transition>

		<transition name="fade">
			<TemplateAddEditPane v-if="panelContentName === 'template-view'" :editId="panelId" @close="closeTemplateViewPanel()" />
		</transition>

		<transition name="fade">
			<ConfigViewPane v-if="panelContentName === 'config-view'" :configId="panelId" @close="closeConfigViewPanel()" />
		</transition>

		<transition name="fade">
			<ApiViewPane v-if="panelContentName === 'api-collections-view'" :editId="panelId" @close="closeApiViewPanel()" />
		</transition>

		<transition name="fade">
			<ChangePulseViewPane v-if="panelContentName === 'change-pulse-view'" :changeId="panelId" @close="closeChangePulseViewPanel()" />
		</transition>

		<transition name="fade">
			<AgentViewPane v-if="panelContentName === 'agent-view'" :editId="panelId" @close="closeAgentViewPanel()" />
		</transition>

		<transition name="fade">
			<RoleAddEditPane v-if="panelContentName === 'role-view'" :editId="panelId" :is-open="true" @close="closeRoleViewPanel()" />
		</transition>

		<transition name="fade">
			<PolicyViewPane v-if="panelContentName === 'policy-definition-details'" :editId="panelId" @close="closePolicyViewPane()" />
		</transition>

		<transition name="fade">
			<PolicyDefinitionAddEditPane v-if="panelContentName === 'policy-definition-view'" :editId="panelId" @close="closePolicyDefinitionPanel()" />
		</transition>

		<transition name="fade">
			<PolicyReportsViewPanel v-if="panelContentName === 'compliance-details'" :editId="panelId" @close="closePolicyReportsViewPanel()" />
		</transition>

		<transition name="fade">
			<PolicyResultsDetailsPane v-if="panelContentName === 'compliance-results-details'" :editId="panelId" @close="closePolicyReportsViewPanel()" />
		</transition>

		<transition name="fade">
			<CicDefinitionAddEditPane v-if="panelContentName === 'cic-definition-view'" :editId="panelId" @close="closeCicDefinitionPanel()" />
		</transition>

		<transition name="fade">
			<PolicyHelpDocument v-if="panelContentName === 'policy-definition-help'" :documentType="panelId" @close="closePolicyHelpPanel(panelId)" />
		</transition>
	</div>
</template>
