<script setup>
import { ref, watch } from "vue";

const props = defineProps({
	items: { type: Array, required: true }, // [{ label, to, icon? }]
	modelValue: { type: String, default: null },
	persistKey: { type: String, default: null },
});
const emit = defineEmits(["update:modelValue", "select"]);

const active = ref(props.modelValue ?? (props.persistKey ? localStorage.getItem(props.persistKey) : null) ?? props.items?.[0]?.to ?? null);

watch(
	() => props.modelValue,
	(v) => {
		if (v && v !== active.value) active.value = v;
	}
);

function select(to) {
	active.value = to;
	if (props.persistKey) localStorage.setItem(props.persistKey, to);
	emit("update:modelValue", to);
	emit("select", to);
}

function isActive(to) {
	return active.value === to;
}
</script>

<template>
	<div class="flex items-center">
		<Button v-for="item in items" :key="item.to" type="button" :data-nav="item.to" variant="ghost" class="relative ml-2 border" @click="select(item.to)">
			<RcIcon v-if="item.icon" :name="item.icon" class="mr-2" />
			{{ item.label }}

			<!-- subtle indicator -->
			<span class="pointer-events-none absolute left-0 right-0 -bottom-[6px] h-0.5 bg-blue-500 origin-center transition-transform duration-200" :class="isActive(item.to) ? 'scale-x-100' : 'scale-x-0'" />
		</Button>
	</div>
</template>
