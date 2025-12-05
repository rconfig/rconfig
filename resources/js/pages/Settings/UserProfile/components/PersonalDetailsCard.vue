<script setup>
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Save } from "lucide-vue-next";
import { usePersonalDetails } from "./usePersonalDetails";

const props = defineProps({
	user: {
		type: Object,
		required: true,
	},
	profileUserId: {
		type: String,
		required: true,
	},
});

const { personalDetails, isSaving, savePersonalDetails } = usePersonalDetails(props.profileUserId, props.user);
</script>

<template>
	<Card class="bg-transparent border-none">
		<CardHeader>
			<CardTitle class="flex items-center">
				<div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg mr-2">
					<RcIcon name="user" class="h-5 w-5" />
				</div>
				Personal Details
			</CardTitle>
			<CardDescription>Update your personal information</CardDescription>
		</CardHeader>

		<CardContent>
			<form class="space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div class="space-y-2">
						<Label for="firstName">Name</Label>
						<Input id="firstName" v-model="personalDetails.name" placeholder="Your name" class="placeholder-gray-500" />
					</div>

					<div class="space-y-2">
						<Label for="lastName">Username <small>(optional)</small></Label>
						<Input id="lastName" v-model="personalDetails.username" placeholder="Your username" class="placeholder-gray-500" />
					</div>
				</div>
			</form>
		</CardContent>

		<CardFooter class="flex justify-end border-t pt-4">
			<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" variant="primary" @click="savePersonalDetails">
				<Spinner :state="isSaving" class="h-4 w-4 mr-2" />
				<Save v-if="!isSaving" class="h-4 w-4 mr-2" />
				<span>Save Changes</span>
				<div class="pl-2 ml-auto"></div>
			</Button>
		</CardFooter>
	</Card>
</template>
