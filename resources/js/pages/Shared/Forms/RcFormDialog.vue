<script setup>
import RcFormDialogI18N from "@/i18n/pages/Shared/Forms/RcFormDialog.i18n.js";
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { computed } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";

const props = defineProps({
	modelValue: Object,
	errors: Object,
	title: {
		type: String,
		default: "Form Dialog",
	},
	isOpen: {
		type: Boolean,
		required: true,
	},
	editId: {
		type: [Number, null],
		default: 0,
	},
	requiredFields: {
		type: Array,
		default: () => ["name"],
	},
});

const emit = defineEmits(["update:modelValue", "close", "save"]);

const isEditMode = computed(() => props.editId > 0);
const { t } = useComponentTranslations(RcFormDialogI18N);
</script>

<template>
	<Dialog :open="isOpen">
		<DialogContent class="w-full p-0 bg-background text-foreground border border-border">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-muted-foreground">
					{{ isEditMode ? t("common.edit") : t("common.add") }} – {{ title }} <span v-if="isEditMode">(ID: {{ editId }})</span>
				</DialogTitle>
			</DialogHeader>

			<div class="grid gap-2 p-4">
				<div v-for="field in requiredFields" :key="field" class="grid items-center grid-cols-4 gap-2">
					<Label :for="field" class="text-right capitalize"> {{ field }} <span class="text-red-600">*</span> </Label>
					<Input :id="field" v-model="modelValue[field]" class="col-span-3" />
					<div class="rc-text-xs-muted col-span-3 col-start-2">{{ t("enter") }} {{ field }}</div>
					<span v-if="errors && errors[field]" class="col-span-3 col-start-2 text-sm text-red-400">
						{{ errors[field][0] }}
					</span>
				</div>
			</div>

			<DialogFooter class="rc-dialog-footer bg-muted">
				<Button type="button" variant="outline" class="px-2 py-1 ml-2 text-sm" @click="emit('close')" size="sm">
					{{ t("common.cancel") }}
				</Button>
				<Button type="submit" variant="primary" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700" size="sm" @click="emit('save')">
					{{ isEditMode ? t("common.update") : t("common.save") }}
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
