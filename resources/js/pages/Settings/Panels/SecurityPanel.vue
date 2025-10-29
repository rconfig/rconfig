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
			</div>
		</div>
	</div>
</template>