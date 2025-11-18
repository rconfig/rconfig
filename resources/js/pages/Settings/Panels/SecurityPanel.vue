<script setup>
import { Separator } from "@/components/ui/separator";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { UserCheck } from "lucide-vue-next";
import { useSecurityPanel } from "@/pages/Settings/Panels/useSecurityPanel";
import { ExternalLinkComponent } from "@/pages/Shared/Links";
import { Info } from "lucide-vue-next";

// Use the composable
const { isLoading, isSsoLoading, fileStatus, ssoEnabled } = useSecurityPanel();
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-3/4">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="rc-panel-heading">Security</h3>
				<p class="rc-panel-subheading">Manage security settings and understand data responsibility</p>

				<div class="flex items-start gap-4 mt-6">
					<RcIcon name="encryption-shield" />

					<div class="w-3/4">
						<div class="rc-panel-heading3">
							Encryption of Stored Credentials
						</div>
						<div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
							All device credentials stored in the database are encrypted using the APP_KEY encryption key in the
							<code class="px-1 rc-text-code-block">.env</code>
							file.
						</div>
						<div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
							It is important to backup your
							<code class="px-1 rc-text-code-block">.env</code>
							file as without it, you will not be able to decrypt your stored credentials.
						</div>
						<div class="flex items-center gap-2 text-xs text-muted-foreground mt-2">
							<RcIcon name="laravel" />
							<ExternalLinkComponent :to="'https://laravel.com/docs/11.x/configuration'" :text="'Learn more about Laravel .env configuration'" :active="true" />
						</div>
					</div>
				</div>
				<Separator class="my-6" />

				<div class="flex items-start gap-4 mt-6">
					<div class="mr-8">
						<RcIcon name="credentials" :height="48" :width="48" />
					</div>
					<div class="w-3/4">
						<div class="rc-panel-heading3">
							Your Data, Your Responsibility
						</div>
						<div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
							rConfig is designed to be installed on-premises or in your own cloud infrastructure. This means you have complete control over your data and its security.
						</div>
						<div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
							It is your responsibility to ensure that your server is secure, regularly backed up, and that access to the application is properly controlled. We recommend following security best practices including regular updates, strong passwords, and network segmentation.
						</div>
					</div>
				</div>

				<Separator class="my-6" />
				<div v-if="isSsoLoading" class="flex items-center justify-center w-full">
					<Loading />
				</div>
				<div v-if="!isSsoLoading" class="flex items-start gap-4 mt-6">
					<div class="w-12 h-12 mr-8 flex items-center justify-center">
						<UserCheck size="42" class="text-purple-400 dark:text-purple-300" stroke-width="1.5" />
					</div>
					<div class="w-3/4">
						<div class="rc-panel-heading3">
							<span v-if="ssoEnabled" class="flex items-center">
								<svg class="h-5 w-5 text-green-400 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
								</svg>
								Single Sign-On Authentication Active
							</span>
							<span v-else class="flex items-center">
								<svg class="h-5 w-5 text-blue-400 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
								</svg>
								Single Sign-On Authentication Disabled
							</span>
						</div>
						<div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
							<span v-if="ssoEnabled">
								Your rConfig installation is configured with Single Sign-On (SSO) authentication. This allows users to authenticate using their existing organizational credentials, enhancing security by leveraging centralized identity management and reducing the need for multiple passwords.
							</span>
							<span v-else>
								Single Sign-On (SSO) authentication is not currently configured. Setting up SSO can improve security by reducing password fatigue and leveraging your organization's existing identity management systems. Follow the documentation to enable this feature.
							</span>
						</div>
						<div class="flex items-center gap-2 mt-2 text-xs text-muted-foreground">
							<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
							</svg>
							<ExternalLinkComponent :to="$rconfigDocsUrl + '/integrations/sso/sso-overview/'" :text="'Learn more about SSO configuration'" :active="true" />
						</div>
					</div>
				</div>
				<Separator class="my-6" />
			</div>
		</div>
	</div>
</template>