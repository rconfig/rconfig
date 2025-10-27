<script setup>
import { ref } from "vue";
import { DiamondMinus } from "lucide-vue-next";
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
	<RcToolTip :delayDuration="100" :content="'Agent Down'" :side="'bottom'">
		<template #trigger>
			<DiamondMinus :width="width" :height="height" :color="color" />
		</template>
	</RcToolTip>
</template>
