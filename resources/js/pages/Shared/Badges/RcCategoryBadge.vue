<script setup>
defineProps({
	category: {
		type: Object,
		default: () => null,
	},
	placeholder: {
		type: String,
		default: "Uncategorized",
	},
});
</script>
<template>
	<div class="flex justify-start w-full">
		<span :class="category?.badgeColor ? category?.badgeColor : 'bg-gray-600 text-gray-200 border-gray-500'" class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border">
			{{ category?.categoryName || placeholder }}
		</span>
	</div>
</template>
