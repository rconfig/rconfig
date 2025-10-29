<script setup>
import { ref, onMounted } from "vue";
import { usePermissionsStore } from "@/stores/permissions";
//import ModalRoleMissingNotice from "@/pages/Errors/ModalRoleMissingNotice.vue";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
const modalState = ref(false);
const permissionsCount = ref(0);
const isVisible = ref(false);

onMounted(() => {
	permissionsCount.value = usePermissionsStore().permissionsCount;
	if (permissionsCount.value === 0) {
		modalState.value = true;
	}
	console.log("403 Forbidden");

	// Trigger entrance animation
	setTimeout(() => {
		isVisible.value = true;
	}, 100);
});

function close() {
	modalState.value = false;
}
</script>

<template>
	<main class="min-h-screen bg-gradient-to-br from-rcgray-50 via-rcgray-100 to-rcgray-200 dark:from-rcgray-900 dark:via-rcgray-800 dark:to-rcgray-700 flex items-center justify-center p-6 relative overflow-hidden">
		<!-- Animated background elements -->
		<div class="absolute inset-0 overflow-hidden pointer-events-none">
			<div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-rcgray-200/10 to-rcgray-300/10 rounded-full blur-3xl delay-500"></div>
		</div>

		<!-- Main content -->
		<div class="relative z-10 w-full max-w-2xl mx-auto">
			<div class="transform transition-all duration-1000 ease-out" :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'">
				<!-- Error code with glowing effect -->
				<div class="text-center mb-8">
					<div class="inline-block relative">
						<h1 class="text-8xl md:text-9xl font-black bg-gradient-to-r from-red-500 via-pink-500 to-purple-600 bg-clip-text text-transparent">
							403
						</h1>
						<div class="absolute inset-0 text-8xl md:text-9xl font-black text-red-500/20 blur-sm delay-300">
							403
						</div>
					</div>
				</div>

				<!-- Main card with glassmorphism effect -->
				<Card class="backdrop-blur-xl bg-white/70 dark:bg-rcgray-800/70 border-white/20 dark:border-rcgray-600/20 shadow-2xl shadow-rcgray-500/10 hover:shadow-rcgray-500/20 transition-all duration-500">
					<CardHeader class="text-center pb-6">
						<!-- Animated lock icon -->
						<div class="flex justify-center mb-6">
							<div class="relative">
								<div class="w-20 h-20 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center shadow-lg">
									<svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
									</svg>
								</div>
								<div class="absolute inset-0 w-20 h-20 bg-gradient-to-r from-red-500 to-pink-500 rounded-full blur-md opacity-50 animate-ping"></div>
							</div>
						</div>

						<CardTitle class="text-4xl md:text-3xl font-bold bg-gradient-to-r from-rcgray-800 to-rcgray-600 dark:from-white dark:to-rcgray-300 bg-clip-text text-transparent mb-3">
							{{ t("title") }}
						</CardTitle>
						<CardDescription class="text-lg text-rcgray-600 dark:text-rcgray-300 leading-relaxed">
							{{ t("subtitle") }}
						</CardDescription>
					</CardHeader>

					<CardContent class="text-center space-y-6">
						<div class="bg-gradient-to-r from-rcgray-50 to-rcgray-100 dark:from-rcgray-700 dark:to-rcgray-600 rounded-2xl p-6 border border-rcgray-200 dark:border-rcgray-600">
							<div class="flex items-center justify-center space-x-3 mb-3">
								<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
								</svg>
								<h3 class="font-semibold text-rcgray-800 dark:text-white">{{ t("sections.whatCanYouDoTitle") }}</h3>
							</div>
							<p class="text-rcgray-600 dark:text-rcgray-300 text-sm leading-relaxed">
								{{ t("sections.whatCanYouDoDescription") }}
							</p>
						</div>

						<div class="flex flex-col sm:flex-row gap-3 justify-center">
							<router-link to="/dashboard">
								<Button class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center justify-center space-x-2">
									<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
									</svg>
									<span>{{ t("buttons.goToDashboard") }}</span>
								</Button>
							</router-link>

							<router-link to="/settings/about">
								<Button variant="outline" class="w-full sm:w-auto border-2 border-rcgray-300 dark:border-rcgray-600 hover:border-rcgray-400 dark:hover:border-rcgray-500 px-8 py-3 rounded-xl font-semibold hover:bg-rcgray-50 dark:hover:bg-rcgray-700 transform hover:scale-105 transition-all duration-200 flex items-center justify-center space-x-2">
									<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
									</svg>
									<span>{{ t("buttons.contactSupport") }}</span>
								</Button>
							</router-link>
						</div>

						<!-- Status indicator -->
						<div class="flex items-center justify-center space-x-2 text-sm text-rcgray-500 dark:text-rcgray-400 pt-4 border-t border-rcgray-200 dark:border-rcgray-600">
							<div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
							<span>{{ t("statusIndicator") }}</span>
						</div>
					</CardContent>
				</Card>
			</div>
		</div>

		<!-- Modal if no permissions -->
		<!-- <ModalRoleMissingNotice v-if="modalState" ref="modalRoleMissingNotice" @close="close" /> -->
	</main>
</template>

<style scoped>
@keyframes float {
	0%,
	100% {
		transform: translateY(0px);
	}
	50% {
		transform: translateY(-10px);
	}
}

.animate-float {
	animation: float 3s ease-in-out infinite;
}
</style>
