<script setup>
</script>

<template>
	<div class="hidden xl:block xl:col-span-8">
		<div class="bg-white dark:bg-rcgray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 h-full flex flex-col items-center justify-center text-center transition-all duration-300 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-lg cursor-pointer group">
			<!-- Genie Lamp Icon -->
			<div class="mb-4 transform transition-transform duration-300 group-hover:scale-110">
				<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-400 dark:text-gray-500 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors duration-300">
					<!-- Genie Lamp -->
					<path d="M20 40C20 35.5817 23.5817 32 28 32H36C40.4183 32 44 35.5817 44 40V44C44 46.2091 42.2091 48 40 48H24C21.7909 48 20 46.2091 20 44V40Z" stroke="currentColor" stroke-width="2" fill="none" />
					<!-- Lamp Handle -->
					<path d="M44 36C48 36 50 34 52 30C54 26 52 22 48 22C44 22 42 24 42 28" stroke="currentColor" stroke-width="2" fill="none" />
					<!-- Lamp Spout -->
					<path d="M20 38C16 38 14 36 12 32C10 28 12 24 16 24C20 24 22 26 22 30" stroke="currentColor" stroke-width="2" fill="none" />
					<!-- Magic Sparkles -->
					<circle cx="32" cy="18" r="1" fill="currentColor" class="animate-pulse" />
					<circle cx="38" cy="14" r="1" fill="currentColor" class="animate-pulse" style="animation-delay: 0.5s;" />
					<circle cx="26" cy="16" r="1" fill="currentColor" class="animate-pulse" style="animation-delay: 1s;" />
					<!-- Magic Smoke/Wisps -->
					<path d="M30 24C30 22 32 20 34 22C36 24 34 26 32 24" stroke="currentColor" stroke-width="1" fill="none" opacity="0.6" class="animate-pulse" />
				</svg>
			</div>

			<!-- Title -->
			<h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
				V8 Dashboard Preview
			</h3>

			<!-- Description -->
			<p class="text-sm text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
				Help us design the perfect dashboard for rConfig V8
			</p>

			<!-- Call to Action -->
			<div class="bg-gray-50 dark:bg-rcgray-700 rounded-lg px-4 py-2 border border-gray-200 dark:border-gray-600 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 group-hover:border-blue-300 dark:group-hover:border-blue-600 transition-all duration-300">
				<span class="text-xs text-gray-600 dark:text-gray-300 font-medium group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors duration-300">
					Coming Soon
				</span>
			</div>
		</div>
	</div>
</template>

<style scoped>
.animate-pulse {
	animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
	0%,
	100% {
		opacity: 1;
	}
	50% {
		opacity: 0.3;
	}
}
</style>