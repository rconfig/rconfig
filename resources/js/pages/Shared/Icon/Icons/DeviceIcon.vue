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
	class: {
		type: [String, Object, Array],
		default: "",
	},
});
</script>

<template>
	<RcToolTip :delayDuration="100" :content="'Devices Icon'" :side="'bottom'">
		<template #trigger>
			<svg xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" :class="class" viewBox="0 0 16 16">
				<g fill="none" stroke-linecap="round" stroke-linejoin="round">
					<path stroke="#8adf6f" d="M10.5 5a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
					<path stroke="#a6da95" d="M6.5 11a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
					<path stroke="#86aa7a" d="M14.5 11a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" stroke-width="1" />
				</g>
			</svg>
		</template>
	</RcToolTip>
</template>
