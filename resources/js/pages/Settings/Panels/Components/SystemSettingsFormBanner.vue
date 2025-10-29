<script setup>
import { Flag } from "lucide-vue-next";
import { Separator } from "@/components/ui/separator";
import { Textarea } from "@/components/ui/textarea";
import { onMounted } from "vue";
import { useSystemSettingsBanner } from "@/pages/Settings/Panels/Components/useSystemSettingsBanner";

const { banner, getLoginBanner, saveBanner, resetBanner } = useSystemSettingsBanner();

const props = defineProps({});

onMounted(() => {
	getLoginBanner();
});
</script>

<template>
	<div class="grid w-full max-w-full items-center gap-1.5">
		<h3 class="rc-panel-heading">
			<div class="flex items-center">
				<Flag color="#eed49f" /><span class="ml-2">Login Banner</span>
			</div>
		</h3>
		<p class="rc-panel-subheading">Customize the banner message displayed on the login page</p>

		<Textarea :placeholder="banner || 'Enter your login banner message here...'" v-model="banner" />

		<div class="flex justify-end jap-2">
			<Button variant="outline" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" @click.prevent="saveBanner()" size="sm">
				Save
			</Button>

			<Button variant="outline" class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800 hover:animate-pulse" @click.prevent="resetBanner" size="sm">
				Reset
			</Button>
		</div>
		<Separator class="my-6" />
	</div>
</template>
