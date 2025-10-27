<script setup>
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
	<RcToolTip :delayDuration="100" :content="'Disabled'" :side="'bottom'">
		<template #trigger>
			<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none" :stroke="color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-minus-icon lucide-circle-minus">
				<circle cx="12" cy="12" r="10" />
				<path d="M8 12h8" />
			</svg>
		</template>
	</RcToolTip>
</template>
