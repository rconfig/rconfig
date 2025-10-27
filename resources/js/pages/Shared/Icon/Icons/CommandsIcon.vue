<script setup>
import { Terminal } from "lucide-vue-next";
import RcToolTip from "@/pages/Shared/Tooltips/RcToolTip.vue";

const props = defineProps({
	width: {
		type: Number,
		default: 16,
	},
	height: {
		type: Number,
		default: 16,
	},
	class: {
		type: String,
		default: "text-green-300",
	},
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Commands Icon'" :side="'bottom'">
		<template #trigger>
			<Terminal :width="width" :height="height" :class="class" />
		</template>
	</RcToolTip>
</template>
