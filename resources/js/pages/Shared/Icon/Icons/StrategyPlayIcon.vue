<script setup>
import { ref, watch } from "vue";

const props = defineProps({
	height: {
		type: Number,
		default: 200,
	},
	width: {
		type: Number,
		default: 200,
	},
	color: {
		type: String,
		default: "#8087a2",
	},
});

const currentColor = ref(props.color);

// Watch for prop changes
watch(
	() => props.color,
	(newColor) => {
		currentColor.value = newColor;
	}
);
</script>

<template>
	<div class="animated-icon-container">
		<svg id="icon" xmlns="http://www.w3.org/2000/svg" :width="width" :height="height" viewBox="0 0 32 32">
			<g id="x-left" class="x-left">
				<polygon id="x-left-poly" :fill="currentColor" points="12 3.415 10.586 2 7 5.587 3.414 2 2 3.415 5.586 7 2 10.586 3.414 12 7 8.414 10.586 12 12 10.586 8.414 7 12 3.415" />
			</g>
			<path id="path-element" class="path-element" :fill="currentColor" d="m25,2l-5,5,1.4089,1.4189,2.5911-2.625v9.2061H8c-1.1028,0-2,.8975-2,2v3.1011c-2.2793.4644-4,2.4844-4,4.8989,0,2.7568,2.2429,5,5,5s5-2.2432,5-5c0-2.4146-1.7207-4.4346-4-4.8989v-3.1011h16c1.1028,0,2-.8975,2-2V5.8472l2.5911,2.5718,1.4089-1.4189-5-5Zm-15,23c0,1.6543-1.3457,3-3,3s-3-1.3457-3-3,1.3457-3,3-3,3,1.3457,3,3Z" />
			<g id="x-right" class="x-right">
				<polygon id="x-right-poly" :fill="currentColor" points="30 21.415 28.586 20 25 23.587 21.414 20 20 21.415 23.586 25 20 28.586 21.414 30 25 26.414 28.586 30 30 28.586 26.414 25 30 21.415" />
			</g>
			<rect fill="none" width="32" height="32" />
		</svg>

		<div v-if="showColorPicker" class="color-picker">
			<label for="color-input">Change Color:</label>
			<input type="color" id="color-input" v-model="currentColor" @input="$emit('update:color', currentColor)" />
		</div>
	</div>
</template>

<style scoped>
.animated-icon-container {
	display: flex;
	flex-direction: column;
	align-items: center;
}

#icon {
	display: block;
}

.x-left {
	transform-origin: 7px 7px;
	animation: pulse 3s infinite alternate;
}

.x-right {
	transform-origin: 25px 25px;
	animation: pulse 3s infinite alternate-reverse;
}

.path-element {
	animation: shimmer 4s infinite;
}

@keyframes pulse {
	0% {
		transform: scale(1);
		opacity: 1;
	}
	50% {
		transform: scale(0.95);
		opacity: 0.8;
	}
	100% {
		transform: scale(1.05);
		opacity: 1;
	}
}

@keyframes shimmer {
	0% {
		fill-opacity: 1;
	}
	50% {
		fill-opacity: 0.8;
	}
	100% {
		fill-opacity: 1;
	}
}

.color-picker {
	margin-top: 20px;
	text-align: center;
}

label {
	font-family: sans-serif;
	margin-right: 10px;
}
</style>
