<script setup>
import { ref } from "vue";
import { Diamond } from "lucide-vue-next";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";

const props = defineProps({
	height: {
		type: Number,
		default: 16,
	},
	width: {
		type: Number,
		default: 16,
	},
	color: {
		type: String,
		default: "#8087a2",
	},
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Agent Disabled'" :side="'bottom'">
		<template #trigger>
			<Diamond :width="width" :height="height" :color="color" />
		</template>
	</RcToolTip>
</template>
