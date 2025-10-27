<script setup>
import { computed } from "vue";
import { Tags } from "lucide-vue-next";
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
const combinedClass = computed(() => {
	return `text-orange-300 ${props.class}`;
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Tags Icon'" :side="'bottom'">
		<template #trigger>
			<Tags :width="width" :height="height" :class="combinedClass" />
		</template>
	</RcToolTip>
</template>
