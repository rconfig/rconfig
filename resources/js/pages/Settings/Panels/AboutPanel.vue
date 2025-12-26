<script setup>
import AlertTip from "@/pages/Shared/Alerts/AlertTip.vue";
import { onMounted, computed } from "vue";
import { useDashboard } from "@/pages/Dashboard/useDashboard";

// Import Lucide Vue Next icons
import { FileText, Headphones, HelpCircle } from "lucide-vue-next";

const image = computed(() => "/images/brand/rconfig-with-strap-white.png");

const { fetchConfiginfo, configinfo } = useDashboard();

onMounted(() => {
	fetchConfiginfo();
});
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-3/4">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<div class="flex justify-start w-full mb-8">
					<img :src="image" alt="rConfig Logo" class="w-full max-w-xs" />
				</div>
				<h3 class="mb-4 text-2xl font-semibold leading-7 tracking-tight font-inter">About rConfig</h3>
				<p class="text-sm">rConfig is a powerful and user-friendly network configuration management tool designed to help you efficiently manage and automate your network devices. Stay organized, save time, and ensure consistency across your network infrastructure with rConfig.</p>

				<div class="grid gap-3 mt-2 p-4 border">
					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Edition</dt>
							<dd class="flex items-center gap-2">
								<span class="font-medium text-green-600 dark:text-green-400">
									V8 Core (Free)
								</span>
							</dd>
						</div>
					</dl>

					<dl class="grid gap-3">
						<div class="flex items-center justify-between">
							<dt class="text-muted-foreground">Device Count</dt>
							<dd class="flex items-center gap-2">
								<span class="font-medium text-muted-foreground"> 
									{{ configinfo.data?.deviceCount || 0 }}
								</span>
							</dd>
						</div>
					</dl>
				</div>
			</div>

			<!-- Core edition information -->
			<AlertTip 
				class="mt-2" 
				variant="info" 
				small 
				title="rConfig Core Edition" 
				message="You are using rConfig Core, the free edition. Upgrade to Professional or Enterprise for advanced features including extended device support, priority support, and enhanced automation capabilities." 
			/>

			<Separator class="mt-6 xl:mb-2" />
			<div class="flex flex-col items-start justify-between w-full lg:items-start xl:flex-row gap-3">
				<a href="https://www.rconfig.com/editions" class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" target="_blank">
					<FileText size="16" />
					Compare Editions
				</a>
				<Separator class="hidden mx-4 xl:block" orientation="vertical" />
				<a href="https://github.com/rconfig/rconfig/issues" class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" :href="$rconfigPortalUrl" target="_blank">
					<Headphones size="16" />
					Community Support
				</a>
				<Separator class="hidden mx-4 xl:block" orientation="vertical" />
				<a class="flex items-center gap-2 px-4 py-2 text-white rounded-md bg-rcgray-900 hover:bg-rcgray-800 transition-colors" :href="$rconfigDocsUrl" target="_blank">
					<HelpCircle size="16" />
					Online Help
				</a>
			</div>
			<p class="mt-4 ml-4">© rConfig {{ new Date().getFullYear() }}. All rights reserved.</p>
		</div>
	</div>
</template>