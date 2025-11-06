<script setup>
import DeviceViewPane from "@/pages/Inventory/Devices/DeviceView/DeviceViewPane.vue";
import ConfigViewPane from "@/pages/Configs/ConfigView/ConfigViewPane.vue";
import TemplateAddEditPane from "@/pages/Inventory/Templates/TemplateAddEditPane.vue";
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

function closeTemplateViewPanel() {
	panelContentName.value = null;
	router.push({ name: "templates" });
}

function closeConfigViewPanel() {
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
	</div>
</template>