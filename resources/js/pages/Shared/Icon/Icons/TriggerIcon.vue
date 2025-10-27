<script setup>
import { computed } from "vue";
import { Repeat } from "lucide-vue-next";
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
		default: "",
	},
});

// Combine default class with incoming class
const combinedClass = computed(() => {
	return `text-teal-400 ${props.class}`;
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Config Change Push or Pull'" :side="'bottom'">
		<template #trigger>
			<Repeat :width="width" :height="height" :class="combinedClass" />
		</template>
	</RcToolTip>
</template>
