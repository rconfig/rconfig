<script setup>
import { shallowRef, watch, markRaw } from "vue";
import { defineAsyncComponent } from "vue";

const props = defineProps({
	name: {
		type: String,
		required: true,
	},
	width: {
		type: Number,
		default: 16,
	},
	height: {
		type: Number,
		default: 16,
	},
	class: {
		type: [String, Object, Array],
		default: "",
	},
	addedBy: {
		type: [String, Number],
		default: 0,
	},
	animate: {
		type: Boolean,
		default: false,
	},
});

// Prevent Vue from making the component reactive
const iconComponent = shallowRef(null);

function toPascalCase(str) {
	return str
		.split(/[-_ ]+/)
		.map((s) => s.charAt(0).toUpperCase() + s.slice(1))
		.join("");
}

watch(
	() => props.name,
	(name) => {
		const pascalName = `${toPascalCase(name)}Icon`;
		const asyncComp = defineAsyncComponent(() => import(`./Icons/${pascalName}.vue`));
		iconComponent.value = markRaw(asyncComp);
	},
	{ immediate: true }
);
</script>

<template>
	<component :is="iconComponent" :width="width" :height="height" :class="class" :addedBy="addedBy" :animate="animate" />
</template>
