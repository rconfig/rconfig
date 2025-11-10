<script setup>
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { Card, CardContent } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { ShieldUser, Info, ExternalLink, KeyRound } from "lucide-vue-next";
import { onMounted } from "vue";
import { useIntegrations } from "@/pages/Settings/Integrations/useIntegrations";

// Use the integrations composable
const { isLoading, ssoIntegrations, isIntegrationConfigured, openLink, initializeIntegrations } = useIntegrations();

onMounted(() => {
	initializeIntegrations();
});
</script>

<template>  
	<div class="pb-16 pl-10 space-y-6 md:block">
		<div class="space-y-0.5">
			<h2 class="flex items-center space-x-2 text-2xl font-bold tracking-tight">
				<RcIcon name="integrations" class="w-8 h-8" />
				<span>Integrations</span>
			</h2>
			<p class="text-muted-foreground">Setup integrations with systems for security and credentials</p>
		</div>
		<Separator class="my-6" />

		<div class="flex justify-center w-full">
			<div class="flex flex-col items-center w-full gap-4 md:w-3/4">
				<div class="grid w-full max-w-full items-center gap-1.5">
					<h3 class="rc-panel-heading">rConfig Integrations</h3>
					<p class="text-sm text-muted-foreground">Setup integrations with systems for security and credentials</p>

					<div v-if="isLoading" class="flex items-center justify-center w-full">
						<Loading />
					</div>

					<!-- SSO INTEGRATIONS CARD -->
					<div v-if="!isLoading" class="flex items-start gap-4 mt-6">
						<ShieldUser color="#eed49f" size="24" />
						<div class="w-3/4">
							<div>
								<strong>SSO Integrations</strong>
							</div>
							<div class="text-sm text-gray-500 dark:text-gray-400">
								<span class="text-gray-500 dark:text-gray-400">
									SSO Integrations allow you to use your existing SSO provider to authenticate users in rConfig. This is useful for organizations that want to centralize user management and authentication.
								</span>
							</div>
							<div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
								<Info color="#8aadf4" size="12" />
								<a :href="$rconfigDocsUrl + '/integrations/sso/sso-overview/'" target="_blank" class="text-blue-500 underline">
									Learn more about SSO Integrations
								</a>
							</div>
							<Card class="mt-4">
								<CardContent>
									<div class="space-y-6 mt-4">
										<!-- SSO INTEGRATIONS -->
										<div class="rounded-md border p-4" v-for="integration in ssoIntegrations" :key="integration.id">
											<div class="flex items-center">
												<div class="flex-shrink-0">
													<component v-if="integration.iconComponent" :is="integration.iconComponent" class="w-6 h-6" />
												</div>
												<div class="ml-2 flex items-center justify-between w-full">
													<div>
														<h3 class="text-sm font-medium text-muted-foreground">{{ integration.name }}</h3>
													</div>
													<div class="flex items-center justify-end w-full">
														<Button variant="outline" size="sm" @click="openLink(integration)" class="flex items-center gap-1 text-xs" :disabled="isLoading">
															Configure
															<span v-if="integration.external_url === 1" class="ml-1.5">
																<ExternalLink class="w-4 h-4" />
															</span>
														</Button>

														<!-- Show green icon if integration is configured -->
														<RcIcon name="status-green" v-if="isIntegrationConfigured(integration)" class="w-6 h-6 ml-2" />
														<!-- Show gray icon if integration is not configured -->
														<RcIcon name="status-gray" v-else class="w-6 h-6 ml-2" />
													</div>
												</div>
											</div>
										</div>
									</div>
								</CardContent>
							</Card>
						</div>
					</div>
					<Separator class="my-6" />
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.2s ease;
}
.fade-enter,
.fade-leave-to {
	opacity: 0;
}
</style>
