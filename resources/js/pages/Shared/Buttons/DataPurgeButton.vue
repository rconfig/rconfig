<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
const props = defineProps({
	srcName: {
		type: String,
		required: true,
		default: "",
	},
});

function dataPurgeRoute(src) {
	router.push({
		name: "settings-data-purge",
		query: {
			src: src,
		},
	});
}
</script>

<template>
	<Button variant="outline" size="sm" class="group ml-2 hidden lg:inline-flex" @click="dataPurgeRoute(srcName)" title="Purge & Retention" alt="Purge & Retention">
		<RcIcon name="purge" class="2xl:mr-2 h-4 w-4" />
		<span class="hidden 2xl:inline-flex">Purge and Retention</span>
	</Button>
</template>
