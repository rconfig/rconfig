<script setup>
import DeviceViewPane from "@/pages/Inventory/Devices/DeviceView/DeviceViewPane.vue";
import ApiViewPane from "@/pages/Inventory/ApiCollections/ApiView/ApiViewPane.vue";
import ConfigViewPane from "@/pages/Configs/ConfigView/ConfigViewPane.vue";
import TemplateAddEditPane from "@/pages/Inventory/Templates/TemplateAddEditPane.vue";
import RoleAddEditPane from "@/pages/Settings/Rbac/RoleAddEditPane.vue";
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();
const emit = defineEmits(["close"]);
const panelId = ref(0);
const panelContentName = ref(null);
const referringPage = ref(null);

const props = defineProps({});

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
			<RoleAddEditPane v-if="panelContentName === 'role-view'" :editId="panelId" :is-open="true" @close="closeRoleViewPanel()" />
		</transition>
	</div>
</template>