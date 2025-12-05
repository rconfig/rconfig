<script setup>
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { TriangleAlert, RefreshCw } from "lucide-vue-next";

function goToDashboard() {
	router.push("/dashboard");
}

function refreshPage() {
	fetchUserProfile();
}
</script>

<template>
	<Card class="w-full lg:w-1/2 border-rose-400/20">
		<CardHeader class="bg-red-400/15 dark:bg-red-400/20 border-b border-red-400/20">
			<CardTitle class="flex items-center text-rose-400">
				<TriangleAlert class="h-5 w-5 mr-2" />
				Invalid User Profile
			</CardTitle>
		</CardHeader>
		<CardContent class="pt-6">
			<div class="flex items-center mb-6">
				<div class="bg-rose-400/10 p-3 rounded-full mr-4">
					<TriangleAlert class="h-8 w-8 text-rose-400" />
				</div>
				<div>
					<p class="text-lg font-semibold text-rose-400">Profile Not Found</p>
					<p class="text-muted-foreground">We couldn't find the requested profile or you don't have permission to access it.</p>
				</div>
			</div>

			<p class="text-muted-foreground mb-3">
				There might be several reasons for this error:
			</p>
			<ul class="list-disc pl-5 mb-6 space-y-1 text-muted-foreground">
				<li>The user account doesn't exist</li>
				<li>Your session might have expired</li>
				<li>You have insufficient permissions</li>
			</ul>
		</CardContent>
		<CardFooter class="flex flex-col sm:flex-row gap-3 border-t pt-4">
			<Button @click="refreshPage" variant="destructive" class="w-full sm:w-auto">
				<RefreshCw class="h-4 w-4 mr-2" />
				Try Again
			</Button>
			<Button @click="goToDashboard" variant="outline" class="w-full sm:w-auto">
				Back to Dashboard
			</Button>
		</CardFooter>
	</Card>
</template>
