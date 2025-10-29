<script setup>
import Step4CronForm from "@/pages/Tasks/WizardPanels/Step4CronForm.vue";
import Step4I18N from "@/i18n/pages/Tasks/WizardPanels/Step4.i18n.js";
import cronstrue from "cronstrue";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ref, onMounted, watch, watchEffect } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const props = defineProps({
	model: Object,
});

const cronExampleArray = ref(null);
const cronToHuman = ref("");

const { t } = useComponentTranslations(Step4I18N);

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
		<h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">{{ t("selectTaskSchedule") }}</h3>

		<div class="grid w-full max-w-xl items-center gap-1.5">
			<Label for="picture">{{ t("exampleCRONs") }}</Label>
			<Select v-model="cronExampleArray">
				<SelectTrigger class="w-full focus:outline-none focus:ring-0">
					<SelectValue :placeholder="t('selectExampleCronOption')" />
				</SelectTrigger>
				<SelectContent class="">
					<SelectGroup>
						<SelectLabel>{{ t("selectAnOption") }}</SelectLabel>
						<SelectItem value="* * * * *">{{ t("everyMinute") }} (* * * * *)</SelectItem>
						<SelectItem value="*/5 * * * *">{{ t("every5Minutes") }} (*/5 * * * *)</SelectItem>
						<SelectItem value="0,30 * * * *">{{ t("twiceAnHour") }} (0,30 * * * *)</SelectItem>
						<SelectItem value="0 * * * *">{{ t("onceAnHour") }} (0 * * * *)</SelectItem>
						<SelectItem value="0 0,12 * * *">{{ t("twiceADay") }} (0 0,12 * * *)</SelectItem>
						<SelectItem value="0 0 * * *">{{ t("onceADay") }} (0 0 * * *)</SelectItem>
						<SelectItem value="0 0 * * 0">{{ t("onceAWeek") }} (0 0 * * 0)</SelectItem>
						<SelectItem value="0 0 1,15 * *">{{ t("firstAnd15th") }} (0 0 1,15 * *)</SelectItem>
						<SelectItem value="0 0 1 * *">{{ t("onceAMonth") }} (0 0 1 * *)</SelectItem>
						<SelectItem value="0 0 1 1 *">{{ t("onceAYear") }} (0 0 1 1 *)</SelectItem>
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
