<script setup lang="ts">
import axios from "axios";
import { onMounted } from "vue";
import { Popover, PopoverTrigger } from "@/components/ui/popover";
import { XCircle } from "lucide-vue-next";

const emit = defineEmits(["update:modelValue"]);

onMounted(() => {});

function clearFilters() {
	emit("update:modelValue", []);
}
</script>

<template>
	<Popover>
		<PopoverTrigger>
			<Button
				variant="ghost"
				class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400"
				@click="clearFilters"
			>
				<XCircle class="w-4 h-4 lg:mr-2 text-rcgray-400" />
				<span class="hidden lg:inline-flex">Clear Filter</span>
			</Button>
		</PopoverTrigger>
	</Popover>
</template>
