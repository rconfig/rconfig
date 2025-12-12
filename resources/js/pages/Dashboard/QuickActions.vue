<script setup>
import { useRouter } from "vue-router";
import { useToaster } from "@/composables/useToaster";

const router = useRouter();
const { toastSuccess, toastError, toastInfo } = useToaster();

defineProps({});

const purgeFailedConfigs = () => {
	axios
		.post("/api/device/purge-failed-configs", {
			device_id: "--all",
		})
		.then((response) => {
			console.log("✅ Purge successful:", response);
			toastSuccess("Purge Failed Configs", "Purge job started successfully");
		})
		.catch((error) => {
			console.error("❌ Purge failed:", error);
			toastError("Error", "Purge job failed to start");
		});
};

const addDevice = () => {
	router.push({ name: "inventory", params: { view: "devices" }, query: { openDeviceDialog: true } });
};

const viewDevices = () => {
	router.push({ name: "inventory", params: { view: "devices" } });
};

const configSearch = () => {
	router.push({ name: "configsearch" });
};

const openSettings = () => {
	router.push({ name: "settings" });
};

</script>

<template>
	<div class="xl:col-span-12">
		<div class="border-0 shadow-md rounded-2xl bg-card text-card-foreground p-2 md:p-4 transition-all duration-200 hover:shadow-lg">
			<h3 class="text-lg font-semibold hidden md:inline-flex mb-1 flex items-center gap-2">
				<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
				</svg>
				Quick Actions
			</h3>

			<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-1 md:gap-2 lg:gap-3">
				<button @click="addDevice" class="flex items-center gap-2 2xl:gap-3 p-2 md:p-2 2xl:p-2 rounded-xl bg-muted/50 hover:bg-green-50 dark:hover:bg-green-900/30 hover:scale-105 transition-all duration-200 text-left group">
					<div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/50 group-hover:bg-green-200 dark:group-hover:bg-green-800/70 transition-colors duration-200">
						<svg class="w-4 h-4 text-green-600 dark:text-green-400 group-hover:text-green-700 dark:group-hover:text-green-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
						</svg>
					</div>
					<div>
						<div class="font-medium text-sm group-hover:text-green-700 dark:group-hover:text-green-300 transition-colors duration-200">Add Device</div>
						<div class="text-xs text-muted-foreground hidden 2xl:block">Register new device</div>
					</div>
				</button>

				<button @click="viewDevices" class="flex items-center gap-2 2xl:gap-3 p-2 md:p-2 2xl:p-2 rounded-xl bg-muted/50 hover:bg-gray-50 dark:hover:bg-gray-900/30 hover:scale-105 transition-all duration-200 text-left group">
					<div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-900/50 group-hover:bg-gray-200 dark:group-hover:bg-gray-800/70 transition-colors duration-200">
						<svg class="w-4 h-4 text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
						</svg>
					</div>
					<div>
						<div class="font-medium text-sm group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-200">View Devices</div>
						<div class="text-xs text-muted-foreground hidden 2xl:block">Device inventory</div>
					</div>
				</button>

				<button @click="configSearch" class="flex items-center gap-2 2xl:gap-3 p-2 md:p-2 2xl:p-2 rounded-xl bg-muted/50 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:scale-105 transition-all duration-200 text-left group">
					<div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/70 transition-colors duration-200">
						<svg class="w-4 h-4 text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
						</svg>
					</div>
					<div>
						<div class="font-medium text-sm group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors duration-200">Config Search</div>
						<div class="text-xs text-muted-foreground hidden 2xl:block">Find configurations</div>
					</div>
				</button>

				<button @click="purgeFailedConfigs" class="flex items-center gap-2 2xl:gap-3 p-2 md:p-2 2xl:p-2 rounded-xl bg-muted/50 hover:bg-red-50 dark:hover:bg-red-900/30 hover:scale-105 transition-all duration-200 text-left group">
					<div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/50 group-hover:bg-red-200 dark:group-hover:bg-red-800/70 transition-colors duration-200">
						<svg class="w-4 h-4 text-red-600 dark:text-red-400 group-hover:text-red-700 dark:group-hover:text-red-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
						</svg>
					</div>
					<div>
						<div class="font-medium text-sm group-hover:text-red-700 dark:group-hover:text-red-300 transition-colors duration-200">Purge Failed</div>
						<div class="text-xs text-muted-foreground hidden 2xl:block">Clean configs</div>
					</div>
				</button>

				<button @click="openSettings" class="flex items-center gap-2 2xl:gap-3 p-2 md:p-2 2xl:p-2 rounded-xl bg-muted/50 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:scale-105 transition-all duration-200 text-left group">
					<div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/70 transition-colors duration-200">
						<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
						</svg>
					</div>
					<div>
						<div class="font-medium text-sm group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors duration-200">Settings</div>
						<div class="text-xs text-muted-foreground hidden 2xl:block">Configure system</div>
					</div>
				</button>
			</div>
		</div>
	</div>
</template>

<style scoped>
/* Add subtle elevation instead of borders */
.shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .shadow-md {
	box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.18);
}

@keyframes pulse {
	0%,
	100% {
		opacity: 1;
	}
	50% {
		opacity: 0.5;
	}
}

.animate-pulse {
	animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>