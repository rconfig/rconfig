<script setup>
import { computed } from "vue";

const props = defineProps({
	source: {
		type: String,
		default: "Integration",
	},
	width: {
		type: Number,
		default: 16,
	},
	height: {
		type: Number,
		default: 16,
	},
	addedBy: {
		type: [String, Number],
		default: 0,
	},
});

const title = computed(() => {
	return `${props.source} imported device`;
});

const isZabbix = computed(() => props.addedBy == 9000);
const isNetbox = computed(() => props.addedBy == 9001);
</script>

<style scoped>
.imported-device-icon {
	display: inline-flex;
	align-items: center;
	margin-right: 6px;
	color: #60a5fa; /* Blue color for the icon */
}

.zabbix-icon {
	color: #e11d48; /* Red color for Zabbix */
	font-weight: bold;
}

.netbox-icon {
	color: #3b82f6; /* Blue color for Netbox */
	font-weight: bold;
}
</style>

<template>
	<!-- Zabbix Icon -->
	<svg v-if="isZabbix" xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" class="mr-2" :title="`Zabbix imported device`">
		<rect width="16" height="16" rx="3" fill="#d40000" fill-opacity="0.1" />
		<text x="5" y="12" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="#d40000">Z</text>
	</svg>

	<!-- Netbox Icon -->
	<svg v-else-if="isNetbox" xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" class="mr-2" :title="`Netbox imported device`">
		<rect width="16" height="16" rx="3" fill="#1581f4" fill-opacity="0.1" />
		<text x="5" y="12" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="#1581f4">N</text>
	</svg>

	<!-- Default Integration Icon (Without Animation) -->
	<svg v-else xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16" class="mr-2" :title="title">
		<g fill="none" stroke="#c6a0f6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
			<path d="M12.6 3.4A6.5 6.5 0 1 0 14 5.5" />
			<path d="M10.5 8A2.5 2.5 0 0 1 8 10.5A2.5 2.5 0 0 1 5.5 8A2.5 2.5 0 0 1 8 5.5A2.5 2.5 0 0 1 10.5 8M13 3.5a.5.5 0 0 1-.5.5a.5.5 0 0 1-.5-.5a.5.5 0 0 1 .5-.5a.5.5 0 0 1 .5.5" />
		</g>
	</svg>
</template>
