<script setup>
import TimeLocaleCard from "@/pages/Settings/UserProfile/components/TimeLocaleCard.vue";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import IsInvalidUserProfCard from "@/pages/Settings/UserProfile/components/IsInvalidUserProfCard.vue";
import IsUnauthorizedUserProfCard from "@/pages/Settings/UserProfile/components/IsUnauthorizedUserProfCard.vue";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import NotificationPreferences from "@/pages/Settings/UserProfile/components/NotificationPreferences.vue";
import PasswordCard from "@/pages/Settings/UserProfile/components/PasswordCard.vue";
import PersonalDetailsCard from "@/pages/Settings/UserProfile/components/PersonalDetailsCard.vue";
import { Bell } from "lucide-vue-next";
import { Separator } from "@/components/ui/separator";
import { inject, ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useUserProfile } from "@/pages/Settings/UserProfile/useUserProfile";

const route = useRoute();
const router = useRouter();
const loggedInUser = inject("userid");
const profileUserId = route.params.userId;

const { user, isLoading, isAuthorized, isUnauthorized, isInvalid, fetchUserProfile } = useUserProfile(profileUserId, loggedInUser);

// Tab management with persistence
const STORAGE_KEY = "user-profile-active-tab";
const activeTab = ref(localStorage.getItem(STORAGE_KEY) || "profile");

// Watch for tab changes and persist to localStorage
watch(activeTab, (newTab) => {
	localStorage.setItem(STORAGE_KEY, newTab);
});

// Clear stored tab if we're on a different user's profile
onMounted(() => {
	const storedUserId = localStorage.getItem("user-profile-user-id");
	if (storedUserId !== profileUserId.toString()) {
		localStorage.setItem("user-profile-user-id", profileUserId.toString());
		activeTab.value = "profile";
		localStorage.setItem(STORAGE_KEY, "profile");
	}
});
</script>

<template>
	<div class="pb-4 pl-10 space-y-6 md:block">
		<div class="space-y-0.5">
			<h2 class="flex items-center space-x-2 text-2xl font-bold tracking-tight">
				<RcIcon name="settings" class="w-8 h-8" />
				<span>My User Profile</span>
			</h2>
			<p class="text-muted-foreground">Manage your application settings and preferences</p>
		</div>
		<Separator class="my-6" />
	</div>

	<div class="flex flex-col h-full gap-1">
		<div v-if="isLoading" class="flex flex-col w-full">
			<Loading class="mx-auto" />
		</div>

		<div v-else-if="isUnauthorized" class="flex items-center justify-center h-full p-4">
			<IsUnauthorizedUserProfCard :userId="profileUserId" :loggedInUser="loggedInUser" />
		</div>

		<div v-else-if="isInvalid" class="flex items-center justify-center h-full p-4">
			<IsInvalidUserProfCard />
		</div>

		<!-- Authorized - User Profile Content with Tabs -->
		<div v-else-if="isAuthorized" class="container mx-auto px-4 max-w-4xl">
			<Tabs v-model="activeTab" class="w-full">
				<TabsList class="grid w-full grid-cols-2 mb-4">
					<TabsTrigger value="profile">
						<div class="flex items-center justify-center gap-2 data-[state=active]:bg-background">
							<RcIcon name="user" class="w-4 h-4" />
							<span class="hidden sm:inline">Profile & Security</span>
						</div>
					</TabsTrigger>
					<TabsTrigger value="notifications">
						<div class="flex items-center justify-center gap-2 data-[state=active]:bg-background">
							<Bell class="w-4 h-4" />
							<span class="hidden sm:inline">Notifications</span>
						</div>
					</TabsTrigger>
				</TabsList>

				<!-- Profile & Security Tab -->
				<TabsContent value="profile" class="space-y-4 mt-0">
					<div class="tab-content-wrapper">
						<PersonalDetailsCard v-if="user" :user="user" :profileUserId="profileUserId" />
						<PasswordCard v-if="user" :profileUserId="profileUserId" />
						<TimeLocaleCard v-if="user" :user="user" :profileUserId="profileUserId" />
					</div>
				</TabsContent>

				<!-- Notifications Tab -->
				<TabsContent value="notifications" class="space-y-4 mt-0">
					<div class="tab-content-wrapper">
						<NotificationPreferences v-if="user" :profileUserId="user.id" :notificationStatus="user.get_notifications"/>
					</div>
				</TabsContent>
			</Tabs>
		</div>
	</div>
</template>

<style scoped>
.tab-content-wrapper {
	animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(10px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* Ensure smooth transitions for tab content */
.fade-enter-active,
.fade-leave-active {
	transition: all 0.3s ease;
}

.fade-enter-from {
	opacity: 0;
	transform: translateY(10px);
}

.fade-leave-to {
	opacity: 0;
	transform: translateY(-10px);
}

/* Custom tab styling for better visual hierarchy */
:deep(.tabs-list) {
	background-color: hsl(var(--muted));
	border-radius: 8px;
	padding: 4px;
}

:deep(.tabs-trigger) {
	border-radius: 6px;
	transition: all 0.2s ease;
	font-weight: 500;
}

:deep(.tabs-trigger:hover) {
	background-color: hsl(var(--muted) / 0.8);
}

:deep(.tabs-trigger[data-state="active"]) {
	background-color: hsl(var(--background));
	box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

/* Responsive tab content */
@media (max-width: 640px) {
	:deep(.tabs-list) {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	:deep(.tabs-trigger) {
		padding: 8px 12px;
		font-size: 14px;
	}
}
</style>
