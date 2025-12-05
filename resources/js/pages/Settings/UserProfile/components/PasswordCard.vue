<script setup>
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { Save, Clock } from "lucide-vue-next";
import { usePasswordManagement } from "./usePasswordManagement";

const props = defineProps({
	profileUserId: {
		type: String,
		required: true,
	},
});

const { canChangePassword, changePassword, passwordVisibility, passwords, passwordsMatch, isSaving } = usePasswordManagement(props.profileUserId);
</script>

<template>
	<Card class="bg-transparent border-none">
		<CardHeader class="pt-0">
			<CardTitle class="flex items-center">
				<Clock class="h-5 w-5 mr-2" />
				Password
			</CardTitle>
			<CardDescription>Update your password</CardDescription>
		</CardHeader>

		<CardContent>
			<form class="space-y-4">
				<div class="space-y-2">
					<Label for="currentPassword">Current Password</Label>
					<div class="relative">
						<InputPassword id="currentPassword" v-model="passwords.current" :type="passwordVisibility.current ? 'text' : 'password'" />
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div class="space-y-2">
						<Label for="newPassword">New Password</Label>
						<div class="relative">
							<InputPassword id="newPassword" v-model="passwords.new" :type="passwordVisibility.new ? 'text' : 'password'" />
						</div>
					</div>

					<div class="space-y-2">
						<Label for="confirmPassword">Confirm New Password</Label>
						<div class="relative">
							<InputPassword id="confirmPassword" v-model="passwords.confirm" :type="passwordVisibility.confirm ? 'text' : 'password'" />
						</div>
						<p v-if="passwordsMatch === false" class="text-xs text-destructive">
							Passwords don't match
						</p>
					</div>
				</div>
			</form>
		</CardContent>

		<CardFooter class="flex justify-end border-t pt-4">
			<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" variant="primary" @click="changePassword" :disabled="!canChangePassword">
				<Spinner :state="isSaving" class="h-4 w-4 mr-2" />
				<Save v-if="!isSaving" class="h-4 w-4 mr-2" />
				<span>Save Changes</span>
				<div class="pl-2 ml-auto"></div>
			</Button>
		</CardFooter>
	</Card>
</template>
