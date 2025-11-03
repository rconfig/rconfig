<script setup>
import Step4CronForm from "@/pages/Tasks/WizardPanels/Step4CronForm.vue";
import cronstrue from "cronstrue";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onMounted, watch, watchEffect } from "vue";

const props = defineProps({
	model: Object,
});

const cronExampleArray = ref(null);
const cronToHuman = ref("");

onMounted(() => {
	if (props.model.task_cron != "") {
		cronExampleArray.value = props.model.task_cron.join(" ");
	}
});

watch(cronExampleArray, (newVal, oldVal) => {
	var newValarray = newVal.split(" ");
	props.model.task_cron = newValarray;
});

watchEffect(() => {
	if (props.model.task_cron) {
		cronToHuman.value = cronstrue.toString(props.model.task_cron.join(" "));
	}
});
</script>

<template>
	<div>
		<h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Select Task Schedule</h3>

		<div class="grid w-full max-w-xl items-center gap-1.5">
			<Label for="picture">Example CRON Schedules</Label>
			<Select v-model="cronExampleArray">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue placeholder="Select an example CRON option" />
				</SelectTrigger>
				<SelectContent class="">
					<SelectGroup>
						<SelectLabel>Select an Option</SelectLabel>
						<SelectItem value="* * * * *">Every Minute (* * * * *)</SelectItem>
						<SelectItem value="*/5 * * * *">Every 5 Minutes (*/5 * * * *)</SelectItem>
						<SelectItem value="0,30 * * * *">Twice an Hour (0,30 * * * *)</SelectItem>
						<SelectItem value="0 * * * *">Once an Hour (0 * * * *)</SelectItem>
						<SelectItem value="0 0,12 * * *">Twice a Day (0 0,12 * * *)</SelectItem>
						<SelectItem value="0 0 * * *">Once a Day (0 0 * * *)</SelectItem>
						<SelectItem value="0 0 * * 0">Once a Week (0 0 * * 0)</SelectItem>
						<SelectItem value="0 0 1,15 * *">1st and 15th of Month (0 0 1,15 * *)</SelectItem>
						<SelectItem value="0 0 1 * *">Once a Month (0 0 1 * *)</SelectItem>
						<SelectItem value="0 0 1 1 *">Once a Year (0 0 1 1 *)</SelectItem>
					</SelectGroup>
				</SelectContent>
			</Select>
			<span class="mb-0 text-muted-foreground">
				{{ cronToHuman }} &nbsp;
				<span class="mb-4 text-muted-foreground" v-if="model.task_cron"> ({{ model.task_cron.join(" ") }}) </span>
			</span>
		</div>
		<br />
		<Step4CronForm v-model="model.task_cron" />
	</div>
</template>