<script setup>
import Step2I18N from "@/i18n/pages/Tasks/WizardPanels/Step2.i18n.js";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ref } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

defineProps({
	model: Object,
});

const { t } = useComponentTranslations(Step2I18N);
</script>

<template>
	<div>
		<h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">{{ t("enterTaskDetails") }}</h3>
		<div class="grid gap-2 p-4">
			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="task_name" class="text-right">
					{{ t("taskName") }}
					<span class="text-red-500">*</span>
				</Label>
				<Input v-model="model.task_name" id="task_name" autocomplete="off" class="col-span-3 focus:outline-none focus-visible:ring-0" />
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="task_desc" class="text-right">
					{{ t("common.description") }}
				</Label>
				<Input v-model="model.task_desc" id="task_desc" autocomplete="off" class="col-span-3 focus:outline-none focus-visible:ring-0" />
			</div>
		</div>
	</div>
</template>
