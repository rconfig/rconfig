<script setup>
import { ref } from "vue";
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
	<RcToolTip :delayDuration="100" :content="'Up/Good'" :side="'bottom'">
		<template #trigger>
			<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 16 16">
				<g fill="none" stroke="#a6da95" stroke-linecap="round" stroke-linejoin="round" stroke-width="1">
					<path d="M14.5 8A6.5 6.5 0 0 1 8 14.5A6.5 6.5 0 0 1 1.5 8A6.5 6.5 0 0 1 8 1.5A6.5 6.5 0 0 1 14.5 8" />
					<path d="m4.5 7.5l2.5 3l4.5-5" />
				</g>
			</svg>
		</template>
	</RcToolTip>
</template>
