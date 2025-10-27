<script setup>
const title = "Dashboard";

defineProps({});
</script>

<template>
	<main class="flex flex-col flex-1 gap-4 p-4 lg:gap-6 lg:p-6">
		<div class="flex items-center">
			<h1 class="text-lg font-semibold md:text-2xl">404, that's an error!</h1>
		</div>
		<div class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
			<div class="flex flex-col items-center gap-1 text-center pb-16">
				<h3 class="text-2xl font-bold tracking-tight py-24">
					<RcIcon name="strategy-play" class="w-36 h-36" />
				</h3>
				<p class="text-sm text-muted-foreground">The url {{ $route.path }} does not exist on this server.</p>
				<!-- Updated router link to match outline style -->
				<router-link to="/dashboard" class="inline-flex items-center gap-1 justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 mt-4">
					Go to Dashboard
					<RcIcon name="dashboard" class="w-4 h-4 ml-1.5" />
				</router-link>
			</div>
		</div>
	</main>
</template>
