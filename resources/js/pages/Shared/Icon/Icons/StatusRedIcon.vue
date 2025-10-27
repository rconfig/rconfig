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
		default: "#ed8796",
	},
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Down'" :side="'bottom'">
		<template #trigger>
			<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 24 24" fill="none" :stroke="color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x">
				<circle cx="12" cy="12" r="10" />
				<path d="m15 9-6 6" />
				<path d="m9 9 6 6" />
			</svg>
		</template>
	</RcToolTip>
</template>
