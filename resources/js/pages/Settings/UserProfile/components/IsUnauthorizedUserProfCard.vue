<script setup>
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { ShieldAlert, ArrowLeft } from "lucide-vue-next";

const props = defineProps({
	userId: {
		type: String,
		required: true,
	},
	loggedInUser: {
		type: String,
		required: true,
	},
});

function goToDashboard() {
	router.push("/dashboard");
}

function goToOwnProfile() {
	router.push(`/settings/my-profile/${loggedInUser}`);
}
</script>

<template>
	<Card class="w-full lg:w-1/2 max-w-5xl border-amber-500/20">
		<CardHeader class="bg-amber-500/15 dark:bg-amber-900/30 border-b border-amber-500/20">
			<CardTitle class="flex items-center text-amber-600 dark:text-amber-400">
				<ShieldAlert class="h-5 w-5 mr-2" />
				Access Denied
			</CardTitle>
		</CardHeader>
		<CardContent class="pt-6">
			<div class="flex items-center mb-6">
				<div class="bg-amber-100 dark:bg-amber-900/50 p-3 rounded-full mr-4">
					<ShieldAlert class="h-8 w-8 text-amber-600 dark:text-amber-400" />
				</div>
				<div>
					<p class="text-lg font-semibold text-amber-700 dark:text-amber-400">Security Restriction</p>
					<p class="text-muted-foreground">You can only access your own user profile.</p>
				</div>
			</div>

			<p class="text-muted-foreground mb-6">The profile you're trying to access (ID: {{ userId }}) belongs to another user. For security reasons, you can only view and edit your own profile.</p>
		</CardContent>
		<CardFooter class="flex flex-col sm:flex-row gap-3 border-t pt-4">
			<Button @click="goToOwnProfile" variant="default" class="w-full sm:w-auto">
				<ArrowLeft class="h-4 w-4 mr-2" />
				Go to My Profile
			</Button>
			<Button @click="goToDashboard" variant="outline" class="w-full sm:w-auto">
				Back to Dashboard
			</Button>
		</CardFooter>
	</Card>
</template>
