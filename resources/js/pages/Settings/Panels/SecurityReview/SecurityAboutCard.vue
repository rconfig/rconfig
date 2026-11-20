<script setup>
import { KeyRound, UserCheck, HardDrive, ExternalLink } from "lucide-vue-next";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useSecurityPanel } from "@/pages/Settings/Panels/useSecurityPanel";

// The useful content of the old Settings > Security page.
const { isSsoLoading, ssoEnabled } = useSecurityPanel();
</script>

<template>
	<Card data-testid="security-about">
		<CardHeader class="pb-3">
			<CardTitle class="text-base">
				How rConfig protects your data
			</CardTitle>
		</CardHeader>
		<CardContent class="grid gap-4 sm:grid-cols-3 text-sm">
			<div class="flex gap-3">
				<KeyRound class="h-5 w-5 shrink-0 text-purple-500" />
				<div>
					<p class="font-medium">
						Stored credentials are encrypted
					</p>
					<p class="text-muted-foreground text-xs mt-1">
						Device, enable and SMTP passwords are encrypted with the application key (APP_KEY) in <code>.env</code>. Back up <code>.env</code> safely: without it the credentials cannot be decrypted.
						<a
							:href="`${$rconfigDocsUrl}/system-management/enc-key/`"
							target="_blank"
							rel="noopener noreferrer"
							class="inline-flex items-center gap-0.5 text-blue-600 dark:text-blue-400"
						>APP_KEY <ExternalLink class="h-3 w-3" /></a>
					</p>
				</div>
			</div>
			<div class="flex gap-3">
				<UserCheck class="h-5 w-5 shrink-0 text-purple-500" />
				<div>
					<p class="font-medium">
						Single sign-on {{ isSsoLoading ? "…" : ssoEnabled ? "is active" : "is not in use" }}
					</p>
					<p class="text-muted-foreground text-xs mt-1">
						SSO lets your identity provider handle sign-in, including MFA, which rConfig does not have built in.
						<a
							:href="`${$rconfigDocsUrl}/auth-security/sso-overview/`"
							target="_blank"
							rel="noopener noreferrer"
							class="inline-flex items-center gap-0.5 text-blue-600 dark:text-blue-400"
						>SSO setup <ExternalLink class="h-3 w-3" /></a>
					</p>
				</div>
			</div>
			<div class="flex gap-3">
				<HardDrive class="h-5 w-5 shrink-0 text-purple-500" />
				<div>
					<p class="font-medium">
						Your data, your responsibility
					</p>
					<p class="text-muted-foreground text-xs mt-1">
						rConfig runs on your own servers and never sends your configs or credentials anywhere else. Keeping the server patched, backed up and reachable only by the people who need it is up to you.
					</p>
				</div>
			</div>
		</CardContent>
	</Card>
</template>
