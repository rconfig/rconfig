<script setup>
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ref } from "vue";

defineProps({
	model: Object,
});
</script>

<template>
	<div>
		<h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Enter Task Details</h3>
		<div class="grid gap-2 p-4">
			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="task_name" class="text-right">
					Task Name
					<span class="text-red-500">*</span>
				</Label>
				<Input v-model="model.task_name" id="task_name" autocomplete="off" class="col-span-3 focus:outline-none focus-visible:ring-0" />
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="task_desc" class="text-right">
					Description
				</Label>
				<Input v-model="model.task_desc" id="task_desc" autocomplete="off" class="col-span-3 focus:outline-none focus-visible:ring-0" />
			</div>
		</div>
	</div>
</template>