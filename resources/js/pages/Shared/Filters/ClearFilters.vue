<script setup lang="ts">
import axios from "axios";
import { onMounted } from "vue";
import { Popover, PopoverTrigger } from "@/components/ui/popover";
import { XCircle } from "lucide-vue-next";
import { useI18n } from "vue-i18n";

const emit = defineEmits(["update:modelValue"]);
const { t } = useI18n();

onMounted(() => {});

function clearFilters() {
	emit("update:modelValue", []);
}
</script>

<template>
	<Popover>
		<PopoverTrigger>
			<Button variant="ghost" @click="clearFilters" class="flex items-center justify-center w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700 text-rcgray-400">
				<XCircle class="w-4 h-4 lg:mr-2 text-rcgray-400" />
				<span class="hidden lg:inline-flex">{{ t("common.clearFilter") }}</span>
			</Button>
		</PopoverTrigger>
	</Popover>
</template>
