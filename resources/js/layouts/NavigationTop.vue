<script setup>
import NavOpenButton from "@/pages/Shared/Buttons/NavOpenButton.vue"; 
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "@/components/ui/breadcrumb";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuLabel, DropdownMenuSeparator } from "@/components/ui/dropdown-menu";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";
import { ref, computed, onMounted, inject } from "vue";
import { useColorMode } from "@vueuse/core";
import { usePanelStore } from "@/stores/panelStore"; // Import the Pinia store
import { usePermissionsStore } from "@/stores/permissions";
import { useRoute, useRouter } from "vue-router";
import { useToaster } from "@/composables/useToaster";
import { HelpCircle, Sun, Moon, MoreVertical, Paintbrush } from "lucide-vue-next";

const mode = useColorMode();
const panelStore = usePanelStore(); // Access the panel store
const route = useRoute();
const router = useRouter();
const { toastSuccess, toastInfo } = useToaster();
const isDevMode = import.meta.env.DEV;
const permissionsStore = usePermissionsStore();
const serverDisplayName = inject("serverDisplayName");
const serverDisplayColor = inject("serverDisplayColor");
const serverDisplaySize = inject("serverDisplaySize");

//Flag to control dark mode enforcement - set to true for now.
//When we want to enable light mode in the future, set this to false
const enforceDarkMode = ref(true);

defineProps({
	panelRef: {
		type: Object,
		default: null,
	},
});

onMounted(() => {
	if (enforceDarkMode.value) {
		mode.value = "dark";
		document.documentElement.classList.add("dark");
	}
});

const breadcrumbs = computed(() => {
	return route.meta.breadcrumb || [];
});

const navPanelBtnState = computed(() => {
	return panelStore.panelRef?.isCollapsed;
});

function toggleTheme() {
	if (enforceDarkMode.value) {
		toastInfo("Dark Mode Only", "Light mode is currently disabled. Only dark mode is available.");

		mode.value = "dark";
		document.documentElement.classList.add("dark");
	} else {
		const newMode = mode.value === "dark" ? "light" : "dark";
		mode.value = newMode;

		if (newMode === "dark") {
			document.documentElement.classList.add("dark");
		} else {
			document.documentElement.classList.remove("dark");
		}

		if (newMode === "dark") {
			toastSuccess("Dark Mode Enabled", "The interface has been switched to dark mode.");
		} else {
			toastSuccess("Light Mode Enabled", "The interface has been switched to light mode.");
		}
	}
}

function navToUpgrade() {
	router.push({ name: "settings-about" });
}

function logout() {
	console.log("logout");
	axios
		.post("/logout")
		.then((response) => {
			permissionsStore.resetOnLogout();
			window.location.href = "/login";
		})
		.catch((error) => {
			console.log(error);
		});
}
function openNav() {
	panelStore.panelRef?.isCollapsed ? panelStore.panelRef?.expand() : panelStore.panelRef?.collapse();
}
function navToStyles() {
	if (!isDevMode) {
		toastInfo("Dev Mode Only", "Styles page is only available in development mode");
		return;
	}
	router.push({ name: "StylesPage" });
}

const showServerName = computed(() => {
	return serverDisplayName && serverDisplayName.toString().trim() !== "" && serverDisplayColor && serverDisplayColor.toString().trim() !== "" && serverDisplaySize && serverDisplaySize.toString().trim() !== "";
});
</script>

<template>
	<nav class="bg-rcgray-900 relative">
		<div class="relative flex items-center justify-between w-full max-w-full px-2 py-1">
			<div class="flex items-center ml-4">
				<NavOpenButton @openNav="openNav()" :navPanelBtnState="navPanelBtnState" class="transition-transform duration-300 hover:scale-110" />
				<!-- Breadcrumb -->
				<Breadcrumb>
					<BreadcrumbList>
						<BreadcrumbItem v-for="(item, index) in breadcrumbs" :key="index" class="hover:text-blue-400 transition-colors duration-200">
							<router-link v-if="item.link" :to="item.link">
								{{ item.label }}
							</router-link>
							<span v-else>{{ item.label }}</span>
							<BreadcrumbSeparator v-if="index < breadcrumbs.length - 1">
								<span class="rc-text-sm-muted">/</span>
							</BreadcrumbSeparator>
						</BreadcrumbItem>
					</BreadcrumbList>
				</Breadcrumb>
				<!-- Breadcrumb -->
			</div>

			<span v-if="showServerName" :style="{ color: serverDisplayColor }" :class="`text-${serverDisplaySize} font-semibold mr-4`">{{ serverDisplayName }}</span>

			<div class="mt-1 top-nav-div flex items-center space-x-2">
				<Button v-if="isDevMode" class="hover:bg-rcgray-600 transition-colors duration-200 transform hover:scale-105" @click="navToStyles()" variant="ghost">
					<Paintbrush class="absolute h-[1.2rem] w-[1.2rem] text-blue-400" />
				</Button>

				<TooltipProvider>
					<Tooltip>
						<TooltipTrigger as-child>
							<Button class="hover:bg-rcgray-600 transition-colors duration-200 transform hover:scale-105" @click="navToUpgrade()" variant="ghost">
								<HelpCircle class="absolute h-[1.2rem] w-[1.2rem] text-blue-400" />
							</Button>
						</TooltipTrigger>
						<TooltipContent class="text-white bg-rcgray-800 border border-blue-500/30">
							<p>About</p>
						</TooltipContent>
					</Tooltip>
				</TooltipProvider>

				<TooltipProvider>
					<Tooltip>
						<TooltipTrigger as-child>
							<Button variant="ghost" class="hover:bg-rcgray-600 transition-colors duration-200 transform hover:scale-105 relative overflow-hidden" @click="toggleTheme()">
								<div class="absolute inset-0 bg-blue-500/10 rounded-full filter blur-md opacity-0 hover:opacity-100 transition-opacity duration-300"></div>
								<Sun v-if="enforceDarkMode" class="h-[1.2rem] w-[1.2rem] text-yellow-400" />
								<Sun v-else-if="mode === 'dark'" class="h-[1.2rem] w-[1.2rem] text-yellow-400" />
								<Moon v-else class="h-[1.2rem] w-[1.2rem] text-blue-400" />
							</Button>
						</TooltipTrigger>
						<TooltipContent class="text-white bg-rcgray-800 border border-blue-500/30">
							<p v-if="enforceDarkMode">Dark Mode Enabled</p>
							<p v-else>Toggle {{ mode === "dark" ? "Light Mode" : "Dark Mode" }}</p>
						</TooltipContent>
					</Tooltip>
				</TooltipProvider>

				<DropdownMenu>
					<DropdownMenuTrigger as-child>
						<Button variant="ghost" class="hover:bg-rcgray-600 transition-transform duration-200 hover:scale-105">
							<MoreVertical class="text-gray-300" size="16" />
						</Button>
					</DropdownMenuTrigger>
					<DropdownMenuContent class="bg-rcgray-800 border border-rcgray-700 shadow-lg mr-8">
						<DropdownMenuItem class="p-0 cursor-pointer hover:bg-rcgray-700 transition-colors duration-200">
							<router-link type="button" variant="ghost" :to="'/settings/my-profile/' + $userId" class="flex items-center py-2 ml-2 w-full">
								<RcIcon name="user" class="mr-2 w-5 h-5 text-blue-400" />
								<span class="text-gray-200">My Profile</span>
							</router-link>
						</DropdownMenuItem>
						<DropdownMenuSeparator class="bg-rcgray-700" />
						<DropdownMenuItem class="p-0 cursor-pointer hover:bg-rcgray-700 transition-colors duration-200">
							<Button variant="ghost" class="flex justify-start w-full py-1 pl-2" @click.prevent="logout()">
								<RcIcon name="logout" class="mr-2 text-blue-400" />
								<span class="text-gray-200">Sign Out</span>
							</Button>
						</DropdownMenuItem>
					</DropdownMenuContent>
				</DropdownMenu>
			</div>
		</div>
	</nav>
</template>
