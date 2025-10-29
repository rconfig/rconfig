<script setup>
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { ref, inject, computed } from "vue";
import { useCopy } from "@/composables/useCopy";

const { copyItem, activeCopyIcon } = useCopy();
const formatters = inject("formatters");

// Debugging code steps
const debuggingCode = computed(() => ({
	step1: `cd /var/www/html/rconfig/current`,
	step2: `vi .env`,
	step3: `APP_DEBUG=true`,
	step4: `Save and exit the editor`,
	step5: `php artisan rconfig:clear-all`,
}));
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-4/5">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="rc-panel-heading">Logging & Debugging Settings</h3>
				<p class="rc-panel-subheading">Configure application logging and debugging options</p>

				<div class="flex items-start gap-4 mt-6">
					<RcIcon name="debugging" :height="36" :width="36" />

					<Card class="w-full">
						<CardHeader>
							<CardTitle>Enable Debug Mode</CardTitle>
							<CardDescription>Follow these steps to enable debug mode for troubleshooting issues</CardDescription>
						</CardHeader>

						<CardContent>
							<div class="w-full">
								<!-- Step 1 -->
								<div class="relative rounded-md mt-4">
									<h4 class="font-medium text-sm mb-2 flex items-center">
										Step 1: Navigate to the rConfig directory
										<Button class="h-6 p-1 ml-auto" variant="ghost" title="copy path" @click="copyItem('1', debuggingCode.step1)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['1']" :size="16" />
										</Button>
									</h4>
									<div class="relative flex items-center">
										<span class="pre-container overflow-x-auto">
											<pre v-highlightjs><code class="bash pre-container">{{ debuggingCode.step1 }}</code></pre>
										</span>
									</div>
								</div>

								<!-- Step 2 -->
								<div class="relative rounded-md mt-4">
									<h4 class="font-medium text-sm mb-2 flex items-center">
										Step 2: Open the .env file
										<Button class="h-6 p-1 ml-auto" variant="ghost" title="copy path" @click="copyItem('2', debuggingCode.step2)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['2']" :size="16" />
										</Button>
									</h4>
									<div class="relative flex items-center">
										<span class="pre-container overflow-x-auto">
											<pre v-highlightjs><code class="bash pre-container">{{ debuggingCode.step2 }}</code></pre>
										</span>
									</div>
								</div>

								<!-- Step 3 -->
								<div class="relative rounded-md mt-4">
									<h4 class="font-medium text-sm mb-2 flex items-center">
										Step 3: Change APP_DEBUG to true
										<Button class="h-6 p-1 ml-auto" variant="ghost" title="copy path" @click="copyItem('3', debuggingCode.step3)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['3']" :size="16" />
										</Button>
									</h4>
									<div class="relative flex items-center">
										<span class="pre-container overflow-x-auto">
											<pre v-highlightjs><code class="bash pre-container">{{ debuggingCode.step3 }}</code></pre>
										</span>
									</div>
								</div>

								<!-- Step 4 -->
								<div class="relative rounded-md mt-4">
									<h4 class="font-medium text-sm mb-2 flex items-center">
										Step 4: Save and exit the editor
										<Button class="h-6 p-1 ml-auto" variant="ghost" title="copy path" @click="copyItem('4', debuggingCode.step4)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['4']" :size="16" />
										</Button>
									</h4>
									<div class="relative flex items-center">
										<span class="pre-container overflow-x-auto">
											<pre v-highlightjs><code class="bash pre-container">{{ debuggingCode.step4 }}</code></pre>
										</span>
									</div>
								</div>

								<!-- Step 5 -->
								<div class="relative rounded-md mt-4">
									<h4 class="font-medium text-sm mb-2 flex items-center">
										Step 5: Clear all caches
										<Button class="h-6 p-1 ml-auto" variant="ghost" title="copy path" @click="copyItem('5', debuggingCode.step5)">
											<RcIcon name="copy-transition" :isActive="activeCopyIcon['5']" :size="16" />
										</Button>
									</h4>
									<div class="relative flex items-center">
										<span class="pre-container overflow-x-auto">
											<pre v-highlightjs><code class="bash pre-container">{{ debuggingCode.step5 }}</code></pre>
										</span>
									</div>
								</div>
								<div class="text-sm text-gray-500 dark:text-gray-400 mt-6">
									Remember to set APP_DEBUG=false when you're done troubleshooting, as debug mode can expose sensitive information.
								</div>
							</div>
						</CardContent>
					</Card>
				</div>
				<Separator class="my-6" />
			</div>
		</div>
	</div>
</template>